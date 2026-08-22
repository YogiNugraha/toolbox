<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use App\Models\Tool;
use App\Models\Setting;

#[Layout('layouts.base')]
class ToolsDirectory extends Component
{
    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $category = 'all';

    public function selectCategory($category)
    {
        $this->category = $category;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->category = 'all';
    }

    public function render()
    {
        $allActiveTools = Tool::getActiveTools();
        $totalAllTools = $allActiveTools->count();

        // 1. Categories List with real counts
        $categories = $allActiveTools->groupBy('category')->map(function ($items, $catName) {
            return [
                'name' => $catName,
                'count' => $items->count(),
                'slug' => \Illuminate\Support\Str::slug($catName),
            ];
        })->values();

        // 2. Filtered Tools Query
        $filteredTools = $allActiveTools->filter(function ($tool) {
            // Filter by search query
            $matchSearch = true;
            if (!empty($this->search)) {
                $query = strtolower(trim($this->search));
                $matchSearch = str_contains(strtolower($tool->name), $query) ||
                    str_contains(strtolower((string) $tool->description), $query) ||
                    str_contains(strtolower((string) $tool->category), $query) ||
                    str_contains(strtolower((string) $tool->slug), $query);
            }

            // Filter by category
            $matchCategory = true;
            if ($this->category !== 'all') {
                $matchCategory = strtolower((string) $tool->category) === strtolower($this->category);
            }

            return $matchSearch && $matchCategory;
        });

        // 3. Group filtered tools by category (for clean sectioned directory layout)
        $groupedTools = $filteredTools->groupBy('category');

        $siteName = Setting::get('site_name', Setting::get('brand_name', config('app.name')));
        $siteTagline = Setting::get('site_tagline', Setting::get('brand_tagline', 'Katalog Perkakas Digital'));

        return view('livewire.tools-directory', compact(
            'allActiveTools',
            'totalAllTools',
            'categories',
            'filteredTools',
            'groupedTools',
            'siteName',
            'siteTagline'
        ));
    }
}
