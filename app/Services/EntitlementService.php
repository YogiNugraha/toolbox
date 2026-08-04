<?php

namespace App\Services;

use App\Models\User;
use App\Models\Activity;

class EntitlementService
{
    public function getCurrentPlan(User $user): string
    {
        return $user->activeSubscription() ? 'pro' : 'free';
    }

    public function getRemainingQuota(User $user, string $toolSlug): ?int
    {
        $plan = $this->getCurrentPlan($user);
        $limit = config("plans.{$plan}.limits.{$toolSlug}.daily_quota");

        if ($limit === null) return null; // unlimited

        $usedToday = Activity::where('user_id', $user->id)
            ->where('tool_slug', $toolSlug)
            ->whereDate('created_at', today())
            ->count();

        return max(0, $limit - $usedToday);
    }

    public function isFeatureLocked(User $user, string $toolSlug, string $featureKey): bool
    {
        $plan = $this->getCurrentPlan($user);
        $locked = config("plans.{$plan}.limits.{$toolSlug}.locked_features", []);
        return in_array($featureKey, $locked);
    }

    public function canProcessFile(User $user, string $toolSlug, int $fileSizeBytes): bool
    {
        $plan = $this->getCurrentPlan($user);
        $maxMb = config("plans.{$plan}.limits.{$toolSlug}.max_file_size_mb");
        if ($maxMb === null) return true;
        
        return $fileSizeBytes <= ($maxMb * 1024 * 1024);
    }
}
