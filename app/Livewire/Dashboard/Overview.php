<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Tool;
use Illuminate\Support\Str;

class Overview extends Component
{
    public function render()
    {
        $allActiveTools = Tool::getActiveTools();

        // Get unique categories with counts
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

        // Group active tools by category
        $groupedTools = $allActiveTools->groupBy('category');

        return view('livewire.dashboard.overview', [
            'groupedTools' => $groupedTools,
            'categories' => $categories,
            'totalToolsCount' => $allActiveTools->count(),
        ])->layout('layouts.dashboard');
    }
}
