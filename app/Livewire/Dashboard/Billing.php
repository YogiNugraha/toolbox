<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class Billing extends Component
{
    public function render()
    {
        $user = auth()->user();
        $activeSubscription = $user->activeSubscription();
        $history = $user->subscriptions()->latest()->get();

        return view('livewire.dashboard.billing', [
            'activeSubscription' => $activeSubscription,
            'history' => $history,
        ])->layout('layouts.dashboard');
    }
}
