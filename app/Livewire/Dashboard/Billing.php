<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\On;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Billing extends Component
{
    public function mount()
    {
        if (request('status') === 'success') {
            LivewireAlert::title('Pembayaran berhasil! Paket kamu sudah aktif.')->success()->toast()->position('top-end')->timer(4000)->show();
        } elseif (request('status') === 'pending') {
            LivewireAlert::title('Pembayaran sedang diproses.')->info()->toast()->position('top-end')->show();
        }
    }
    public function render()
    {
        $user = auth()->user();



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
        return redirect()->to(route('pricing'));
    }

    public function syncPayment($orderId = null)
    {
        $pending = $orderId 
            ? auth()->user()->subscriptions()->where('midtrans_order_id', $orderId)->first() 
            : auth()->user()->subscriptions()->where('status', 'pending')->first();
            
        if ($pending) {
            try {
                \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
                \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
                
                $midtransStatus = \Midtrans\Transaction::status($pending->midtrans_order_id);
                $transactionStatus = data_get($midtransStatus, 'transaction_status');
                
                if (in_array($transactionStatus, ['capture', 'settlement'])) {
                    $transactionId = data_get($midtransStatus, 'transaction_id');
                    $paymentType = data_get($midtransStatus, 'payment_type');
                    
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
                    session()->flash('message', 'Berhasil! Pembayaran sudah dikonfirmasi dan paket telah aktif.');
                } else if ($transactionStatus === 'pending') {
                    session()->flash('info', 'Status di Midtrans masih Pending. Silakan lanjutkan pembayaran.');
                } else if ($transactionStatus === 'cancel') {
                    $pending->update(['status' => 'cancelled']);
                    session()->flash('error', 'Pembayaran telah dibatalkan.');
                } else if (in_array($transactionStatus, ['deny', 'expire'])) {
                    $pending->update(['status' => 'failed']);
                    session()->flash('error', 'Pembayaran telah kadaluarsa atau ditolak.');
                } else {
                    session()->flash('info', 'Status saat ini: ' . $transactionStatus);
                }
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), '404')) {
                    session()->flash('info', 'Belum ada data di sistem Midtrans. Silakan klik Selesaikan Pembayaran.');
                } else {
                    session()->flash('error', 'Gagal sinkronisasi: ' . $e->getMessage());
                }
            }
        }
        
        return redirect()->route('dashboard.billing');
    }

    public function confirmCancel()
    {
        LivewireAlert::title('Yakin mau berhenti berlangganan paket ini?')
            ->warning()
            ->toast(false)
            ->position('center')
            ->withConfirmButton('Ya, Berhenti')
            ->withCancelButton('Batal')
            ->onConfirm('cancelSubscription')
            ->show();
    }

    #[On('cancelSubscription')]
    public function cancelSubscription()
    {
        $activeSub = auth()->user()->activeSubscription();
        
        if ($activeSub) {
            $activeSub->update([
                'status' => 'expired',
                'expires_at' => now(), // Mematikan akses paket saat ini juga
            ]);

            \Illuminate\Support\Facades\Mail::to(auth()->user()->email)
                ->queue(new \App\Mail\SubscriptionCancelledMail(auth()->user()));

            LivewireAlert::title('Langganan sudah dibatalkan.')->success()->toast()->position('top-end')->timer(3000)->show();
        }
    }
}
