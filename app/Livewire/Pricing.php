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

    public function checkout()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if already has active subscription
        if ($user->activeSubscription()) {
            return redirect()->route('dashboard.billing')->with('message', 'Anda sudah memiliki paket Pro yang aktif.');
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
            $this->dispatch('snap-token-ready', token: $this->snapToken);
        } catch (\Exception $e) {
            // Handle error, maybe show alert
            session()->flash('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pricing')->layout('layouts.app');
    }
}
