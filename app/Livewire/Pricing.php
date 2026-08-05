<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Subscription;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Str;

class Pricing extends Component
{
    public $snapToken;

    public function mount()
    {
        if (request()->query('action') === 'checkout') {
            $this->checkout();
        }
    }

    public function cancelPending()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $pending = auth()->user()->subscriptions()
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($pending) {
            try {
                Config::$serverKey = config('services.midtrans.server_key');
                Config::$isProduction = config('services.midtrans.is_production');
                \Midtrans\Transaction::cancel($pending->midtrans_order_id);
            } catch (\Exception $e) {
                \Log::warning('Gagal cancel transaksi Midtrans: ' . $e->getMessage());
            }

            $pending->update(['status' => 'cancelled']);
            
            session()->flash('info', 'Pembayaran dibatalkan.');
            return redirect()->route('dashboard.billing');
        }
    }

    public function checkout()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if already has active subscription and not eligible for renewal
        $activeSub = $user->activeSubscription();
        if ($activeSub && now()->diffInDays($activeSub->expires_at, false) > 7) {
            return redirect()->route('dashboard.billing')->with('message', 'Anda sudah memiliki paket Pro yang aktif.');
        }

        $pending = $user->subscriptions()
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($pending && $pending->created_at > now()->subHours(24) && $pending->snap_token) {
            $this->snapToken = $pending->snap_token;
            $this->dispatch('snap-token-ready', token: $this->snapToken);
            return;
        }

        if ($pending) {
            $pending->update(['status' => 'expired']);
        }

        // Setup Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $orderId = 'PRO-' . $user->id . '-' . time() . '-' . Str::random(5);
        $price = config('plans.pro.price');

        // Create pending subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_slug' => 'pro',
            'status' => 'pending',
            'midtrans_order_id' => $orderId,
            'amount' => $price,
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $price,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [[
                'id' => 'pro-' . config('plans.pro.duration_days') . 'd',
                'price' => $price,
                'quantity' => 1,
                'name' => config('plans.pro.midtrans_item_name'),
            ]],
        ];

        try {
            $this->snapToken = Snap::getSnapToken($params);
            $subscription->update(['snap_token' => $this->snapToken]);
            $this->dispatch('snap-token-ready', token: $this->snapToken);
        } catch (\Exception $e) {
            // Handle error, maybe show alert
            session()->flash('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pricing')
            ->layout('layouts.dashboard')
            ->title('Upgrade ke Pro');
    }
}
