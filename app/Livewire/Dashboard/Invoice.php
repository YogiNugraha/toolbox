<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Subscription;

class Invoice extends Component
{
    public $subscription;

    public function mount($order_id)
    {
        if (auth()->check() && auth()->user()->is_admin) {
            $this->subscription = Subscription::with(['user', 'plan'])
                ->where('midtrans_order_id', $order_id)
                ->firstOrFail();
        } else {
            $this->subscription = auth()->user()->subscriptions()
                ->with(['user', 'plan'])
                ->where('midtrans_order_id', $order_id)
                ->firstOrFail();
        }
    }

    public function render()
    {
        return view('livewire.dashboard.invoice')->layout('layouts.dashboard')->title('Invoice #' . $this->subscription->midtrans_order_id);
    }
}
