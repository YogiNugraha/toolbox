<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Plan;
use Livewire\Attributes\On;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Illuminate\Support\Str;

class Plans extends Component
{
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

    public $limits = [];
    public $features = []; // Array of string features
    public $toolsConfig = [];

    public function mount()
    {
        $this->toolsConfig = config('tools', []);
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
                LivewireAlert::title('Minimal harus ada 1 paket default!')->warning()->toast()->position('top-end')->show();
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
            'limits' => $this->limits,
            'features' => $this->features,
        ]);

        $this->isModalOpen = false;
        LivewireAlert::title('Paket berhasil disimpan')->success()->toast()->position('top-end')->show();
    }

    public function confirmDelete($id)
    {
        LivewireAlert::title('Yakin hapus paket ini?')
            ->warning()
            ->text('Paket yang memiliki riwayat transaksi tidak bisa dihapus.')
            ->withConfirmButton('Ya, Hapus')
            ->withCancelButton('Batal')
            ->onConfirm('deletePlan', ['id' => $id])
            ->show();
    }

    #[On('deletePlan')]
    public function deletePlan($data)
    {
        $id = $data['id'];
        $plan = Plan::findOrFail($id);

        if ($plan->is_default) {
            LivewireAlert::title('Tidak bisa hapus paket default!')->error()->toast()->position('top-end')->show();
            return;
        }

        if ($plan->subscriptions()->exists()) {
            LivewireAlert::title('Gagal dihapus')->error()->text('Paket ini punya riwayat transaksi. Sebaiknya dinonaktifkan saja.')->toast(false)->position('center')->show();
            return;
        }

        $plan->delete();
        LivewireAlert::title('Paket berhasil dihapus')->success()->toast()->position('top-end')->show();
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
        LivewireAlert::title('Status paket diubah')->success()->toast()->position('top-end')->timer(3000)->show();
    }

    public function render()
    {
        $plans = Plan::orderBy('sort_order')->get();
        return view('livewire.admin.plans', compact('plans'))
            ->layout('layouts.admin');
    }
}
