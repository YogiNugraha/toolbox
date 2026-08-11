<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Subscription;

#[Layout('layouts.admin')]
class Overview extends Component
{
    public function render()
    {
        $totalUsers = User::count();
        $activeProUsers = User::whereHas('subscriptions', function ($query) {
            $query->where('status', 'active')->where('expires_at', '>', now());
        })->count();

        $totalRevenue = Subscription::whereNotNull('starts_at')->sum('amount');
        $revenueThisMonth = Subscription::whereNotNull('starts_at')
            ->whereMonth('starts_at', now()->month)
            ->whereYear('starts_at', now()->year)
            ->sum('amount');

        $recentTransactions = Subscription::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('livewire.admin.overview', compact(
            'totalUsers',
            'activeProUsers',
            'totalRevenue',
            'revenueThisMonth',
            'recentTransactions'
        ));
    }
}
