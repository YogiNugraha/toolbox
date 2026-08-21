<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Traits\LivewireLineoneAlerts;

#[Layout('layouts.admin')]
class Users extends Component
{
    use WithPagination, LivewireLineoneAlerts;

    public $search = '';
    public $planFilter = 'all'; // all, free, pro
    public $statusFilter = 'all'; // all, active, banned

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingPlanFilter()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function confirmBan($userId)
    {
        abort_if($userId === auth()->id(), 403, 'Tidak bisa ban diri sendiri.');

        $this->confirmDialog(
            'Ban pengguna ini?',
            'Pengguna tidak akan bisa login sampai di-unban lagi.',
            'banUser',
            ['userId' => $userId]
        );
    }

    public function banUser($data)
    {
        $userId = $data['userId'] ?? null;
        if (!$userId) return;

        abort_if($userId === auth()->id(), 403, 'Tidak bisa ban diri sendiri.');

        User::where('id', $userId)->update(['banned_at' => now()]);
        $this->toast('Pengguna telah di-ban.', 'success');
    }

    public function unbanUser($userId)
    {
        User::where('id', $userId)->update(['banned_at' => null]);
        $this->toast('Pengguna telah di-unban.', 'success');
    }

    public function getListeners()
    {
        return [
            'banUser',
        ];
    }

    public function render()
    {
        $usersQuery = User::withSum(['subscriptions' => function($query) {
            $query->whereNotNull('midtrans_transaction_id');
        }], 'amount')
        ->with(['subscriptions' => function($query) {
            $query->where('status', 'active')->where('expires_at', '>', now())->orderBy('expires_at', 'desc');
        }]);

        if ($this->search) {
            $usersQuery->where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->planFilter === 'pro') {
            $usersQuery->whereHas('subscriptions', function($query) {
                $query->where('status', 'active')->where('expires_at', '>', now());
            });
        } elseif ($this->planFilter === 'free') {
            $usersQuery->whereDoesntHave('subscriptions', function($query) {
                $query->where('status', 'active')->where('expires_at', '>', now());
            });
        }

        if ($this->statusFilter === 'banned') {
            $usersQuery->whereNotNull('banned_at');
        } elseif ($this->statusFilter === 'active') {
            $usersQuery->whereNull('banned_at');
        }

        $users = $usersQuery->orderBy('created_at', 'desc')->paginate(15);

        return view('livewire.admin.users', compact('users'));
    }
}
