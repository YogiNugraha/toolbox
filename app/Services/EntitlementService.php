<?php

namespace App\Services;

use App\Models\User;
use App\Models\Activity;

class EntitlementService
{
    public function getCurrentPlan(User $user): \App\Models\Plan
    {
        if ($user->is_admin) {
            $proPlan = \App\Models\Plan::where('slug', 'pro-max')->orWhere('slug', 'pro')->first();
            if ($proPlan) {
                return $proPlan;
            }
        }

        $sub = $user->activeSubscription();
        if ($sub && $sub->plan) {
            return $sub->plan;
        }
        return \App\Models\Plan::where('is_default', true)->firstOrFail();
    }

    public function getRemainingQuota(User $user, string $toolSlug): ?int
    {
        if ($user->is_admin) {
            return null; // Admin has unlimited quota on all tools!
        }

        $plan = $this->getCurrentPlan($user);
        $limit = $plan->limits[$toolSlug]['monthly_quota'] 
            ?? $plan->limits[$toolSlug]['daily_quota'] 
            ?? null;

        if ($limit === null) return null; // unlimited

        $usedThisMonth = Activity::where('user_id', $user->id)
            ->where('tool_slug', $toolSlug)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        return max(0, $limit - $usedThisMonth);
    }

    public function isFeatureLocked(User $user, string $toolSlug, string $featureKey): bool
    {
        if ($user->is_admin || $user->isSubscribed()) {
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
        if ($user->is_admin) {
            return true;
        }

        $plan = $this->getCurrentPlan($user);
        $maxMb = $plan->limits[$toolSlug]['max_file_size_mb'] ?? null;
        if ($maxMb === null) return true;
        
        return $fileSizeBytes <= ($maxMb * 1024 * 1024);
    }
}
