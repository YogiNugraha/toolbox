<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Traits\LivewireLineoneAlerts;

class Billing extends Component
{
    use WithPagination, LivewireLineoneAlerts;

    public $searchTrx = '';
    public $statusFilter = '';
    public $perPage = 10;

    public function updatingSearchTrx()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->searchTrx = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function mount()
    {
        if (request('status') === 'success') {
            $this->toast('Pembayaran berhasil! Paket kamu sudah aktif.', 'success');
        } elseif (request('status') === 'pending') {
            $this->toast('Pembayaran sedang diproses.', 'info');
        }
    }

    public function render()
    {
        $user = auth()->user();

        $activeSubscription = $user->activeSubscription();
        $history = $user->subscriptions()
            ->when($this->searchTrx, function ($query) {
                $query->where(function ($q) {
                    $q->where('midtrans_order_id', 'like', '%' . $this->searchTrx . '%')
                      ->orWhere('plan_slug', 'like', '%' . $this->searchTrx . '%')
                      ->orWhere('status', 'like', '%' . $this->searchTrx . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate($this->perPage);

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
        $this->confirmDialog(
            'Yakin mau berhenti berlangganan paket ini?',
            '',
            'cancelSubscription'
        );
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

            $this->toast('Langganan sudah dibatalkan.', 'success');
        }
    }
}
