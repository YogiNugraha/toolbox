<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class Invoice extends Component
{
    public $subscription;

    public function mount($order_id)
    {
        $this->subscription = auth()->user()->subscriptions()
            ->where('midtrans_order_id', $order_id)
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.dashboard.invoice')->layout('layouts.dashboard')->title('Invoice ' . $this->subscription->midtrans_order_id);
    }
}
