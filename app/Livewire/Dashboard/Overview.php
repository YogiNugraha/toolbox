<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Activity;

class Overview extends Component
{
    public function render()
    {
        $userId = auth()->id();
        $activities = Activity::where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();
            
        $totalFiles = Activity::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();
            
        $totalSaved = Activity::where('user_id', $userId)
            ->where('status', 'completed')
            ->whereNotNull('original_size')
            ->whereNotNull('result_size')
            ->get()
            ->sum(function($activity) {
                return max(0, $activity->original_size - $activity->result_size);
            });

        $todayFiles = Activity::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();

        $entitlementService = app(\App\Services\EntitlementService::class);
        $currentPlan = $entitlementService->getCurrentPlan(auth()->user());
        $activeSub = auth()->user()->activeSubscription();

        return view('livewire.dashboard.overview', [
            'activities' => $activities,
            'totalFiles' => $totalFiles,
            'totalSaved' => $totalSaved,
            'todayFiles' => $todayFiles,
            'currentPlan' => $currentPlan,
            'activeSub' => $activeSub,
            'tools' => config('tools'),
        ])->layout('layouts.dashboard');
    }
}
