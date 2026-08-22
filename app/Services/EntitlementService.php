<?php

namespace App\Services;

use App\Models\User;
use App\Models\Activity;

class EntitlementService
{
    public function getCurrentPlan(User $user): \App\Models\Plan
    {
        $sub = $user->activeSubscription();
        if ($sub && $sub->plan) {
            return $sub->plan;
        }
        return \App\Models\Plan::where('is_default', true)->firstOrFail();
    }

    public function getRemainingQuota(User $user, string $toolSlug): ?int
    {
        $plan = $this->getCurrentPlan($user);
        $limit = $plan->limits[$toolSlug]['daily_quota'] ?? null;

        if ($limit === null) return null; // unlimited

        $usedToday = Activity::where('user_id', $user->id)
            ->where('tool_slug', $toolSlug)
            ->whereDate('created_at', today())
            ->count();

        return max(0, $limit - $usedToday);
    }

    public function isFeatureLocked(User $user, string $toolSlug, string $featureKey): bool
    {
        if ($user->isSubscribed()) {
            return false;
        }

        $plan = $this->getCurrentPlan($user);
        
        if (isset($plan->limits[$toolSlug]['locked_features'])) {
            return in_array($featureKey, $plan->limits[$toolSlug]['locked_features']);
        }

        // Fallback to config if not present in DB record yet
        $lockedConfig = config("plans.{$plan->slug}.limits.{$toolSlug}.locked_features");
        if ($lockedConfig !== null) {
            return in_array($featureKey, $lockedConfig);
        }

        return false;
    }

    public function canProcessFile(User $user, string $toolSlug, int $fileSizeBytes): bool
    {
        $plan = $this->getCurrentPlan($user);
        $maxMb = $plan->limits[$toolSlug]['max_file_size_mb'] ?? null;
        if ($maxMb === null) return true;
        
        return $fileSizeBytes <= ($maxMb * 1024 * 1024);
    }
}
