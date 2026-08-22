<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Subscription;
use App\Models\Plan;
use App\Traits\LivewireLineoneAlerts;

#[Layout('layouts.admin')]
class Transactions extends Component
{
    use WithPagination, LivewireLineoneAlerts;

    public $search = '';
    public $statusFilter = 'all'; // all, active, pending, expired, cancelled, failed
    public $planFilter = 'all'; // all, pro, pro-max, etc.
    public $dateFilter = 'all'; // all, today, this_month, this_year

    public $showDetailModal = false;
    public $selectedTransaction = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    public function updatingPlanFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function openDetailModal($id)
    {
        $this->selectedTransaction = Subscription::with(['user', 'plan'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedTransaction = null;
    }

    public function manualActivate($id)
    {
        $transaction = Subscription::findOrFail($id);
        $transaction->activate();
        $this->toast('Transaksi #' . ($transaction->midtrans_order_id ?? $transaction->id) . ' berhasil diaktifkan.', 'success');
        $this->openDetailModal($id);
    }

    public function render()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        $transactionsQuery = Subscription::with(['user', 'plan']);

        if ($this->search) {
            $transactionsQuery->where(function($query) {
                $query->where('midtrans_order_id', 'like', '%' . $this->search . '%')
                      ->orWhere('midtrans_transaction_id', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                      });
            });
        }

        if ($this->statusFilter !== 'all') {
            $transactionsQuery->where('status', $this->statusFilter);
        }

        if ($this->planFilter !== 'all') {
            $transactionsQuery->where('plan_slug', $this->planFilter);
        }

        if ($this->dateFilter === 'today') {
            $transactionsQuery->whereDate('created_at', now()->today());
        } elseif ($this->dateFilter === 'this_month') {
            $transactionsQuery->whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year);
        } elseif ($this->dateFilter === 'this_year') {
            $transactionsQuery->whereYear('created_at', now()->year);
        }

        $transactions = $transactionsQuery->latest('created_at')->paginate(15);

        // Summary metrics
        $totalRevenue = Subscription::whereNotNull('starts_at')->sum('amount');
        $successCount = Subscription::whereNotNull('starts_at')->count();
        $pendingCount = Subscription::where('status', 'pending')->count();
        $failedCount = Subscription::whereIn('status', ['expired', 'failed', 'cancelled'])->count();

        return view('livewire.admin.transactions', compact(
            'transactions',
            'plans',
            'totalRevenue',
            'successCount',
            'pendingCount',
            'failedCount'
        ));
    }
}
