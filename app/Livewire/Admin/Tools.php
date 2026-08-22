<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Tool;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.admin')]
class Tools extends Component
{
    use WithPagination, WithFileUploads, LivewireLineoneAlerts;

    public $search = '';
    public $selectedCategory = '';
    public $statusFilter = '';
    public $perPage = 15;

    public $isModalOpen = false;
    public $toolId = null;

    // Form fields
    public $name = '';
    public $slug = '';
    public $description = '';
    public $category = 'Image';
    public $icon = 'wrench';
    public $image = '';
    public $imageFile = null;
    public $component = '';
    public $badge = '';
    public $is_pro_only = false;
    public $is_highlighted = false;
    public $is_active = true;
    public $is_maintenance = false;
    public $maintenance_message = '';
    public $sort_order = 0;

    protected $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'category' => 'required|string|max:100',
        'icon' => 'required|string|max:100',
        'image' => 'nullable|string|max:500',
        'imageFile' => 'nullable|image|max:2048', // max 2MB
        'component' => 'nullable|string|max:255',
        'badge' => 'nullable|string|max:50',
        'is_pro_only' => 'boolean',
        'is_highlighted' => 'boolean',
        'is_active' => 'boolean',
        'is_maintenance' => 'boolean',
        'maintenance_message' => 'nullable|string|max:500',
        'sort_order' => 'required|integer',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedCategory = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function updatedName($value)
    {
        if (!$this->toolId && empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    public function create()
    {
        $this->resetForm();
        $this->sort_order = (Tool::max('sort_order') ?? 0) + 10;
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $tool = Tool::findOrFail($id);

        $this->toolId = $tool->id;
        $this->name = $tool->name;
        $this->slug = $tool->slug;
        $this->description = $tool->description ?? '';
        $this->category = $tool->category ?? 'General';
        $this->icon = $tool->icon ?? 'wrench';
        $this->image = $tool->image ?? '';
        $this->component = $tool->component ?? '';
        $this->badge = $tool->badge ?? '';
        $this->is_pro_only = (bool) $tool->is_pro_only;
        $this->is_highlighted = (bool) $tool->is_highlighted;
        $this->is_active = (bool) $tool->is_active;
        $this->is_maintenance = (bool) $tool->is_maintenance;
        $this->maintenance_message = $tool->maintenance_message ?? '';
        $this->sort_order = (int) $tool->sort_order;

        $this->isModalOpen = true;
    }

    public function removeImage()
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            Storage::disk('public')->delete($this->image);
        }
        $this->image = '';
        $this->imageFile = null;
    }

    public function save()
    {
        $rules = $this->rules;
        $rules['slug'] = 'required|string|max:255|unique:tools,slug,' . ($this->toolId ?? 'NULL') . ',id';
        $this->validate($rules);

        $savedImagePath = $this->image;
        if ($this->imageFile) {
            $savedImagePath = $this->imageFile->store('tools', 'public');
        }

        Tool::updateOrCreate(
            ['id' => $this->toolId],
            [
                'name' => $this->name,
                'slug' => Str::slug($this->slug),
                'description' => $this->description,
                'category' => $this->category,
                'icon' => $this->icon,
                'image' => $savedImagePath,
                'component' => $this->component,
                'badge' => $this->badge ? strtoupper(trim($this->badge)) : null,
                'is_pro_only' => (bool) $this->is_pro_only,
                'is_highlighted' => (bool) $this->is_highlighted,
                'is_active' => $this->is_active,
                'is_maintenance' => $this->is_maintenance,
                'maintenance_message' => $this->maintenance_message,
                'sort_order' => (int) $this->sort_order,
            ]
        );

        $this->isModalOpen = false;
        $this->toast($this->toolId ? 'Tool berhasil diperbarui!' : 'Tool baru berhasil ditambahkan!', 'success');
        $this->resetForm();
    }

    public function toggleProOnly($id)
    {
        $tool = Tool::findOrFail($id);
        $tool->update(['is_pro_only' => !$tool->is_pro_only]);

        $statusText = $tool->is_pro_only ? 'dikunci khusus Member PRO (👑 PRO Only)' : 'dibuka untuk Semua Pengguna (Free)';
        $this->toast("Akses tool {$tool->name} berhasil {$statusText}!", 'success');
    }

    public function toggleHighlighted($id)
    {
        $tool = Tool::findOrFail($id);
        $tool->update(['is_highlighted' => !$tool->is_highlighted]);

        $statusText = $tool->is_highlighted ? 'di-highlight ke Beranda' : 'dihapus dari Highlight Beranda';
        $this->toast("Tool {$tool->name} berhasil {$statusText}!", 'success');
    }

    public function toggleActive($id)
    {
        $tool = Tool::findOrFail($id);
        $tool->update(['is_active' => !$tool->is_active]);

        $statusText = $tool->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $this->toast("Tool {$tool->name} berhasil {$statusText}!", 'success');
    }

    public function toggleMaintenance($id)
    {
        $tool = Tool::findOrFail($id);
        $tool->update(['is_maintenance' => !$tool->is_maintenance]);

        $statusText = $tool->is_maintenance ? 'masuk mode Maintenance' : 'kembali Normal';
        $this->toast("Tool {$tool->name} {$statusText}!", 'info');
    }

    public function confirmDelete($id)
    {
        $tool = Tool::findOrFail($id);
        $this->confirmDialog(
            "Hapus Tool {$tool->name}?",
            "Tindakan ini akan menghapus data konfigurasi tool {$tool->name} dari daftar sistem.",
            'deleteToolConfirmed',
            ['id' => $id]
        );
    }

    #[On('deleteToolConfirmed')]
    public function deleteToolConfirmed($data)
    {
        $tool = Tool::findOrFail($data['id']);
        if ($tool->image && Storage::disk('public')->exists($tool->image)) {
            Storage::disk('public')->delete($tool->image);
        }
        $tool->delete();

        $this->toast("Tool {$tool->name} berhasil dihapus!", 'success');
    }

    public function syncFromConfigAction()
    {
        Tool::syncFromConfig();
        $this->toast('Daftar tools berhasil disinkronisasi dari file konfigurasi!', 'success');
    }

    private function resetForm()
    {
        $this->toolId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->category = 'Image';
        $this->icon = 'wrench';
        $this->image = '';
        $this->imageFile = null;
        $this->component = '';
        $this->badge = '';
        $this->is_pro_only = false;
        $this->is_highlighted = false;
        $this->is_active = true;
        $this->is_maintenance = false;
        $this->maintenance_message = '';
        $this->sort_order = 0;
        $this->resetValidation();
    }

    public function render()
    {
        $totalTools = Tool::count();
        $activeToolsCount = Tool::where('is_active', true)->where('is_maintenance', false)->count();
        $maintenanceToolsCount = Tool::where('is_maintenance', true)->count();
        $proToolsCount = Tool::where('is_pro_only', true)->count();
        $totalProcessed = Tool::sum('total_usage_count');

        $categories = Tool::select('category')->distinct()->pluck('category')->filter()->values();

        $tools = Tool::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('category', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedCategory, function ($q) {
                $q->where('category', $this->selectedCategory);
            })
            ->when($this->statusFilter, function ($q) {
                if ($this->statusFilter === 'active') {
                    $q->where('is_active', true)->where('is_maintenance', false);
                } elseif ($this->statusFilter === 'pro_only') {
                    $q->where('is_pro_only', true);
                } elseif ($this->statusFilter === 'highlighted') {
                    $q->where('is_highlighted', true);
                } elseif ($this->statusFilter === 'maintenance') {
                    $q->where('is_maintenance', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.tools', [
            'tools' => $tools,
            'categories' => $categories,
            'totalTools' => $totalTools,
            'activeToolsCount' => $activeToolsCount,
            'maintenanceToolsCount' => $maintenanceToolsCount,
            'proToolsCount' => $proToolsCount,
            'totalProcessed' => $totalProcessed,
        ])->title('Kelola Tools & Fitur - ' . config('app.name'));
    }
}
