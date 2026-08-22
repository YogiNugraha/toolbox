<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Tool;
use Illuminate\Support\Str;

class CategoryTools extends Component
{
    public $category;
    public $search = '';

    public function mount($category)
    {
        $this->category = $category;
    }

    public function render()
    {
        $formattedCategory = str_replace('-', ' ', $this->category);

        $tools = Tool::where('is_active', true)
            ->where(function ($q) use ($formattedCategory) {
                $q->where('category', 'like', $formattedCategory)
                  ->orWhere('category', 'like', $this->category);
            })
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Get nice display name
        $categoryTitle = ucfirst($formattedCategory);
        if (strtolower($formattedCategory) === 'image') {
            $categoryTitle = 'Gambar & Foto';
        } elseif (strtolower($formattedCategory) === 'document') {
            $categoryTitle = 'Dokumen & PDF';
        }

        return view('livewire.dashboard.category-tools', [
            'tools' => $tools,
            'categoryTitle' => $categoryTitle,
            'rawCategory' => $this->category,
        ])->layout('layouts.dashboard');
    }
}
