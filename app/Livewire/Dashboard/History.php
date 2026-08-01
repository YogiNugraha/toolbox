<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Activity;

class History extends Component
{
    use WithPagination;

    public function render()
    {
        $activities = Activity::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('livewire.dashboard.history', [
            'activities' => $activities
        ])->layout('layouts.dashboard');
    }
}
