<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class Billing extends Component
{
    public function render()
    {
        $user = auth()->user();

        // Reconciliation fallback
        $pendingSubscriptions = $user->subscriptions()->where('status', 'pending')->get();
        if ($pendingSubscriptions->count() > 0) {
            \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
            \Midtrans\Config::$isProduction = config('services.midtrans.is_production');

            foreach ($pendingSubscriptions as $subscription) {
                try {
                    $status = \Midtrans\Transaction::status($subscription->midtrans_order_id);
                    if (in_array($status->transaction_status, ['settlement', 'capture']) && ($status->fraud_status ?? 'accept') === 'accept') {
                        $durationDays = config('plans.' . $subscription->plan_slug . '.duration_days');
                        
                        $activeSub = \App\Models\Subscription::where('user_id', $subscription->user_id)
                            ->where('status', 'active')
                            ->where('id', '!=', $subscription->id)
                            ->where('expires_at', '>', now())
                            ->latest('expires_at')
                            ->first();

                        $startsAt = now();
                        $expiresAt = now()->addDays($durationDays);

                        if ($activeSub) {
                            $startsAt = $activeSub->expires_at;
                            $expiresAt = $activeSub->expires_at->copy()->addDays($durationDays);
                            $activeSub->update(['status' => 'expired']);
                        } else {
                            \App\Models\Subscription::where('user_id', $subscription->user_id)
                                ->where('status', 'active')
                                ->where('id', '!=', $subscription->id)
                                ->update(['status' => 'expired']);
                        }
                        
                        $subscription->update([
                            'status' => 'active',
                            'starts_at' => $startsAt,
                            'expires_at' => $expiresAt,
                        ]);

                        // Fix A: Auto-close pending transactions
                        \App\Models\Subscription::where('user_id', $subscription->user_id)
                            ->where('id', '!=', $subscription->id)
                            ->where('status', 'pending')
                            ->update(['status' => 'expired']);
                    } elseif (in_array($status->transaction_status, ['deny', 'cancel', 'expire'])) {
                        $subscription->update(['status' => 'failed']);
                    }
                } catch (\Exception $e) {
                    // Ignore errors (e.g., order not found on midtrans)
                }
            }
        }

        $activeSubscription = $user->activeSubscription();
        $history = $user->subscriptions()->latest()->get();
        $pending = $user->subscriptions()
            ->where('status', 'pending')
            ->where('created_at', '>', now()->subHours(24))
            ->latest()
            ->first();

        return view('livewire.dashboard.billing', [
            'activeSubscription' => $activeSubscription,
            'history' => $history,
            'pending' => $pending,
        ])->layout('layouts.dashboard');
    }

    public function renew()
    {
        return redirect()->to(route('pricing') . '?action=checkout');
    }
}
