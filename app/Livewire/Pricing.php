<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Subscription;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Str;

class Pricing extends Component
{
    public $snapToken;
    public $country_code = '+62';
    public $phone;
    public $showPhoneForm = false;
    public $selectedPlanId;
    
    public function mount()
    {
        if (auth()->check()) {
            $fullPhone = auth()->user()->phone;
            if ($fullPhone) {
                $codes = ['+62', '+1', '+44', '+60', '+65', '+61'];
                foreach ($codes as $code) {
                    if (str_starts_with($fullPhone, $code)) {
                        $this->country_code = $code;
                        $this->phone = substr($fullPhone, strlen($code));
                        break;
                    }
                }
                if (!$this->phone) {
                    $this->phone = $fullPhone;
                }
            }
        }

        $pending = auth()->user()->subscriptions()
            ->where('status', 'pending')
            ->where('created_at', '>', now()->subHours(24))
            ->latest()
            ->first();

        if ($pending && $pending->snap_token) {
            $this->snapToken = $pending->snap_token;
        }
    }

    public function confirmCancelCheckout()
    {
        LivewireAlert::title('Batalkan pembayaran ini?')
            ->warning()
            ->toast(false)
            ->position('center')
            ->withConfirmButton('Ya, Batalkan')
            ->withCancelButton('Tidak')
            ->onConfirm('cancelPending')
            ->show();
    }

    #[On('cancelPending')]
    public function cancelPending()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $pending = auth()->user()->subscriptions()
            ->where('status', 'pending')
            ->where('snap_token', $this->snapToken)
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

    public function savePhone()
    {
        $this->validate([
            'country_code' => 'required|string|max:10',
            'phone' => 'required|string|min:9|max:20',
        ]);

        $cleanPhone = ltrim(preg_replace('/[^0-9]/', '', $this->phone), '0');
        $finalPhone = $this->country_code . $cleanPhone;

        auth()->user()->update(['phone' => $finalPhone]);
        $this->showPhoneForm = false;
        $this->checkout();
    }
    public function selectPlan($planId)
    {
        $this->selectedPlanId = $planId;
        $this->checkout();
    }

    public function checkout()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        $plan = \App\Models\Plan::findOrFail($this->selectedPlanId);

        // Check if already has active subscription and not eligible for renewal
        $activeSub = $user->activeSubscription();
        if ($activeSub && $activeSub->plan_id === $plan->id && now()->diffInDays($activeSub->expires_at, false) > 7) {
            return redirect()->route('dashboard.billing')->with('message', 'Anda sudah memiliki paket ini yang aktif.');
        }

        if (empty($user->phone)) {
            $this->showPhoneForm = true;
            return;
        }

        $pending = $user->subscriptions()
            ->where('status', 'pending')
            ->where('plan_id', $plan->id)
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

        $orderId = strtoupper($plan->slug) . '-' . $user->id . '-' . time() . '-' . Str::random(5);
        $price = $plan->price;

        if ($price == 0) {
            // Langsung aktifkan plan gratis tanpa Midtrans
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_slug' => $plan->slug,
                'status' => 'active',
                'midtrans_order_id' => $orderId,
                'amount' => 0,
            ]);
            $subscription->activate();
            session()->flash('message', 'Paket berhasil diganti.');
            return redirect()->route('dashboard.billing');
        }

        // Create pending subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'plan_slug' => $plan->slug,
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
                'phone' => $user->phone,
            ],
            'item_details' => [[
                'id' => $plan->slug . '-' . ($plan->duration_days ?? 'selamanya'),
                'price' => $price,
                'quantity' => 1,
                'name' => 'ToolBox ' . $plan->name,
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

    public function checkPaymentStatus()
    {
        if ($this->snapToken) {
            $pending = auth()->user()->subscriptions()
                ->where('snap_token', $this->snapToken)
                ->first();
            
            if ($pending) {
                if ($pending->status === 'active') {
                    session()->flash('message', 'Berhasil! Pembayaran Anda telah terverifikasi oleh sistem.');
                    return redirect()->route('dashboard.billing');
                }

                // Fallback: Check Midtrans API directly just in case Webhook is delayed
                try {
                    Config::$serverKey = config('services.midtrans.server_key');
                    Config::$isProduction = config('services.midtrans.is_production');
                    
                    $midtransStatus = \Midtrans\Transaction::status($pending->midtrans_order_id);
                    
                    $transactionStatus = data_get($midtransStatus, 'transaction_status');
                    $transactionId = data_get($midtransStatus, 'transaction_id');
                    $paymentType = data_get($midtransStatus, 'payment_type');

                    if ($midtransStatus && in_array($transactionStatus, ['capture', 'settlement'])) {
                        $updateData = [];
                        if (!empty($transactionId) && empty($pending->midtrans_transaction_id)) {
                            $updateData['midtrans_transaction_id'] = $transactionId;
                        }
                        if (!empty($paymentType) && empty($pending->payment_type)) {
                            $updateData['payment_type'] = $paymentType;
                        }
                        if (!empty($updateData)) {
                            $pending->update($updateData);
                        }
                        $pending->activate();
                        session()->flash('message', 'Berhasil! Pembayaran Anda telah terverifikasi.');
                        return redirect()->route('dashboard.billing');
                    }
                } catch (\Exception $e) {
                    // Ignore errors (e.g., transaction not found yet)
                }
            }
        }
    }

    public function handlePaymentStatus($status)
    {
        if ($status === 'success') {
            $pending = auth()->user()->subscriptions()
                ->where('status', 'pending')
                ->where('snap_token', $this->snapToken)
                ->first();
            
            if ($pending) {
                try {
                    Config::$serverKey = config('services.midtrans.server_key');
                    Config::$isProduction = config('services.midtrans.is_production');
                    
                    $midtransStatus = \Midtrans\Transaction::status($pending->midtrans_order_id);
                    
                    $transactionStatus = data_get($midtransStatus, 'transaction_status');
                    $transactionId = data_get($midtransStatus, 'transaction_id');
                    $paymentType = data_get($midtransStatus, 'payment_type');

                    if ($midtransStatus && in_array($transactionStatus, ['capture', 'settlement'])) {
                        $updateData = [];
                        if (!empty($transactionId) && empty($pending->midtrans_transaction_id)) {
                            $updateData['midtrans_transaction_id'] = $transactionId;
                        }
                        if (!empty($paymentType) && empty($pending->payment_type)) {
                            $updateData['payment_type'] = $paymentType;
                        }
                        if (!empty($updateData)) {
                            $pending->update($updateData);
                        }
                        $pending->activate();
                    }
                } catch (\Exception $e) {
                    \Log::error('Gagal verifikasi dari frontend: ' . $e->getMessage());
                }
            }
            session()->flash('message', 'Berhasil! Pembayaran Anda telah terverifikasi oleh sistem.');
        } elseif ($status === 'pending') {
            session()->flash('info', 'Menunggu Pembayaran! Silakan selesaikan pembayaran Anda. Status akan diperbarui otomatis setelah berhasil.');
        }
        
        return redirect()->route('dashboard.billing');
    }

    public function render()
    {
        $plans = \App\Models\Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('livewire.pricing', compact('plans'))
            ->layout('layouts.dashboard')
            ->title('Upgrade ke Pro');
    }
}
