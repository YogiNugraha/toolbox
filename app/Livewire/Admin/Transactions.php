<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Subscription;

#[Layout('layouts.admin')]
class Transactions extends Component
{
    use WithPagination;

    public $statusFilter = 'all'; // all, active, pending, expired
    public $dateFilter = 'all'; // all, today, this_month, this_year

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $transactionsQuery = Subscription::with('user');

        if ($this->statusFilter !== 'all') {
            $transactionsQuery->where('status', $this->statusFilter);
        }

        if ($this->dateFilter === 'today') {
            $transactionsQuery->whereDate('created_at', now()->today());
        } elseif ($this->dateFilter === 'this_month') {
            $transactionsQuery->whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year);
        } elseif ($this->dateFilter === 'this_year') {
            $transactionsQuery->whereYear('created_at', now()->year);
        }

        $transactions = $transactionsQuery->orderBy('created_at', 'desc')->paginate(15);

        // Summary metrics
        $totalRevenue = Subscription::whereNotNull('midtrans_transaction_id')->sum('amount');
        $successCount = Subscription::where('status', 'active')->count();
        $pendingCount = Subscription::where('status', 'pending')->count();
        $failedCount = Subscription::where('status', 'expired')->whereNotNull('midtrans_transaction_id')->count(); // Only genuine expired ones

        return view('livewire.admin.transactions', compact(
            'transactions',
            'totalRevenue',
            'successCount',
            'pendingCount',
            'failedCount'
        ));
    }
}
