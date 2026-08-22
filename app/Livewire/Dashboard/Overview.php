<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Tool;
use Illuminate\Support\Str;

class Overview extends Component
{
    public $search = '';
    public $selectedCategory = 'all';

    public function selectCategory($category)
    {
        $this->selectedCategory = $category;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedCategory = 'all';
    }

    public function render()
    {
        $allActiveTools = Tool::getActiveTools();

        // Get unique categories with counts and icon colors
        $categories = $allActiveTools->groupBy('category')->map(function ($items, $key) {
            $displayName = $key;
            if (strtolower($key) === 'image') $displayName = 'Gambar & Foto';
            if (strtolower($key) === 'document') $displayName = 'Dokumen & PDF';

            return [
                'raw' => $key,
                'name' => $displayName,
                'slug' => Str::slug($key),
                'count' => $items->count(),
            ];
        })->values();

        // Filter tools based on search and selected category
        $filteredTools = $allActiveTools
            ->when($this->selectedCategory !== 'all', function ($collection) {
                return $collection->filter(function ($tool) {
                    return strtolower($tool->category) === strtolower($this->selectedCategory)
                        || Str::slug($tool->category) === Str::slug($this->selectedCategory);
                });
            })
            ->when($this->search, function ($collection) {
                $q = strtolower(trim($this->search));
                return $collection->filter(function ($tool) use ($q) {
                    return str_contains(strtolower($tool->name), $q)
                        || str_contains(strtolower($tool->description ?? ''), $q)
                        || str_contains(strtolower($tool->slug), $q)
                        || str_contains(strtolower($tool->category), $q);
                });
            });

        // Group filtered tools by category
        $groupedTools = $filteredTools->groupBy('category');

        return view('livewire.dashboard.overview', [
            'groupedTools' => $groupedTools,
            'categories' => $categories,
            'totalToolsCount' => $allActiveTools->count(),
            'filteredCount' => $filteredTools->count(),
        ])->layout('layouts.dashboard');
    }
}
