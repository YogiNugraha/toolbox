<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Plan;
use Livewire\Attributes\On;
use Illuminate\Support\Str;
use App\Traits\LivewireLineoneAlerts;

class Plans extends Component
{
    use LivewireLineoneAlerts;

    public $isModalOpen = false;
    
    public $planId;
    public $slug;
    public $name;
    public $price = 0;
    public $duration_days = null;
    public $description;
    public $is_default = false;
    public $is_active = true;
    public $sort_order = 0;
    
    public $discount_type = 'none';
    public $discount_value = 0;

    public $limits = [];
    public $features = []; // Array of string features
    public $toolsConfig = [];

    // Global Settings for Pricing
    public $is_tax_enabled;
    public $tax_percent;
    public $is_service_fee_enabled;
    public $service_fee_type;
    public $service_fee_value;

    public function mount()
    {
        $this->toolsConfig = \App\Models\Tool::getAllTools()->toArray();
        
        $this->is_tax_enabled = filter_var(\App\Models\Setting::get('is_tax_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $this->tax_percent = \App\Models\Setting::get('tax_percent', 11);
        $this->is_service_fee_enabled = filter_var(\App\Models\Setting::get('is_service_fee_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $this->service_fee_type = \App\Models\Setting::get('service_fee_type', 'fixed');
        $this->service_fee_value = \App\Models\Setting::get('service_fee_value', 2500);
    }

    public function updatedName($value)
    {
        if (!$this->planId && empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $this->resetInputFields();
        $plan = Plan::findOrFail($id);
        $this->planId = $id;
        $this->slug = $plan->slug;
        $this->name = $plan->name;
        $this->price = $plan->price;
        $this->duration_days = $plan->duration_days;
        $this->description = $plan->description;
        $this->is_default = $plan->is_default;
        $this->is_active = $plan->is_active;
        $this->sort_order = $plan->sort_order;
        $this->discount_type = $plan->discount_type;
        $this->discount_value = $plan->discount_value;
        $this->limits = $plan->limits ?? [];
        $this->features = $plan->features ?? [];

        foreach($this->toolsConfig as $tool) {
            $slug = $tool['slug'];
            if (!isset($this->limits[$slug])) {
                $this->limits[$slug] = [
                    'daily_quota' => null,
                    'locked_features' => [],
                    'max_file_size_mb' => null
                ];
            }
        }

        $this->isModalOpen = true;
    }

    public function addFeature()
    {
        $this->features[] = '';
    }

    public function removeFeature($index)
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,' . $this->planId,
            'price' => 'required|integer|min:0',
            'duration_days' => 'nullable|integer|min:1',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'required|integer',
            'discount_type' => 'required|in:none,percent,fixed',
            'discount_value' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string'
        ]);

        // Clean up empty features
        $this->features = array_values(array_filter($this->features, function($f) {
            return trim((string)$f) !== '';
        }));

        // Cast empty strings to null for nullable numbers
        if ($this->duration_days === '') {
            $this->duration_days = null;
        }

        foreach ($this->limits as $slug => $limit) {
            if (isset($this->limits[$slug]['daily_quota']) && $this->limits[$slug]['daily_quota'] === '') {
                $this->limits[$slug]['daily_quota'] = null;
            }
            if (isset($this->limits[$slug]['max_file_size_mb']) && $this->limits[$slug]['max_file_size_mb'] === '') {
                $this->limits[$slug]['max_file_size_mb'] = null;
            }
            // Ensure locked features is an array, if missing make it empty array
            if (!isset($this->limits[$slug]['locked_features'])) {
                $this->limits[$slug]['locked_features'] = [];
            }
        }

        if ($this->is_default) {
            // Unset other defaults
            Plan::where('id', '!=', $this->planId)->update(['is_default' => false]);
        } else {
            // if we are unsetting default, make sure there's another default
            $otherDefaults = Plan::where('id', '!=', $this->planId)->where('is_default', true)->count();
            if ($otherDefaults === 0) {
                $this->is_default = true;
                $this->toast('Minimal harus ada 1 paket default!', 'warning');
            }
        }

        Plan::updateOrCreate(['id' => $this->planId], [
            'slug' => Str::slug($this->slug),
            'name' => $this->name,
            'price' => (int) $this->price,
            'duration_days' => $this->duration_days ? (int) $this->duration_days : null,
            'description' => $this->description,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'discount_type' => $this->discount_type,
            'discount_value' => (int) $this->discount_value,
            'limits' => $this->limits,
            'features' => $this->features,
        ]);

        $this->isModalOpen = false;
        $this->toast('Paket berhasil disimpan', 'success');
    }

    public function confirmDelete($id)
    {
        $this->confirmDialog(
            'Yakin hapus paket ini?',
            'Paket yang memiliki riwayat transaksi tidak bisa dihapus.',
            'deletePlan',
            ['id' => $id]
        );
    }

    #[On('deletePlan')]
    public function deletePlan($data)
    {
        $id = $data['id'];
        $plan = Plan::findOrFail($id);

        if ($plan->is_default) {
            $this->toast('Tidak bisa hapus paket default!', 'error');
            return;
        }

        if ($plan->subscriptions()->exists()) {
            $this->toast('Paket ini punya riwayat transaksi. Sebaiknya dinonaktifkan saja.', 'error');
            return;
        }

        $plan->delete();
        $this->toast('Paket berhasil dihapus', 'success');
    }

    private function resetInputFields()
    {
        $this->planId = null;
        $this->slug = '';
        $this->name = '';
        $this->price = 0;
        $this->duration_days = null;
        $this->description = '';
        $this->is_default = false;
        $this->is_active = true;
        $this->sort_order = 0;
        $this->discount_type = 'none';
        $this->discount_value = 0;
        
        $this->features = [];
        $this->limits = [];
        foreach($this->toolsConfig as $tool) {
            $this->limits[$tool['slug']] = [
                'daily_quota' => null,
                'locked_features' => [],
                'max_file_size_mb' => null
            ];
        }
    }

    public function toggleActive($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->update(['is_active' => !$plan->is_active]);
        $this->toast('Status paket diubah', 'success');
    }

    public function saveSettings()
    {
        $this->validate([
            'is_tax_enabled' => 'boolean',
            'tax_percent' => 'required|numeric|min:0|max:100',
            'is_service_fee_enabled' => 'boolean',
            'service_fee_type' => 'required|in:fixed,percent',
            'service_fee_value' => 'required|numeric|min:0',
        ]);

        \App\Models\Setting::set('is_tax_enabled', $this->is_tax_enabled);
        \App\Models\Setting::set('tax_percent', $this->tax_percent);
        \App\Models\Setting::set('is_service_fee_enabled', $this->is_service_fee_enabled);
        \App\Models\Setting::set('service_fee_type', $this->service_fee_type);
        \App\Models\Setting::set('service_fee_value', $this->service_fee_value);

        $this->toast('Pengaturan biaya berhasil disimpan!', 'success');
    }

    public function render()
    {
        $plans = Plan::orderBy('sort_order')->get();
        return view('livewire.admin.plans', compact('plans'))
            ->layout('layouts.admin');
    }
}
