<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

#[Layout('layouts.admin')]
class Users extends Component
{
    use WithPagination, LivewireLineoneAlerts;

    public $search = '';
    public $planFilter = 'all'; // all, free, pro, pro-max, etc.
    public $statusFilter = 'all'; // all, active, banned

    // Modal Edit / Manage State
    public $showUserModal = false;
    public $selectedUserId = null;
    public $editName = '';
    public $editEmail = '';
    public $editIsAdmin = false;
    public $editEmailVerified = false;
    public $editIsBanned = false;
    public $newPassword = '';
    public $newPasswordConfirmation = '';
    public $generatedPassword = '';

    // Subscription Manage State
    public $editPlanSlug = 'free';
    public $editDurationType = '30'; // 30, 90, 180, 365, lifetime, custom
    public $editExpiresAt = '';

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

    public function openUserModal($userId)
    {
        $user = User::with(['subscriptions' => function($q) {
            $q->where('status', 'active')->where('expires_at', '>', now())->orderBy('expires_at', 'desc');
        }])->findOrFail($userId);

        $this->selectedUserId = $user->id;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editIsAdmin = (bool) $user->is_admin;
        $this->editEmailVerified = (bool) $user->email_verified_at;
        $this->editIsBanned = (bool) $user->banned_at;
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';
        $this->generatedPassword = '';

        $activeSub = $user->subscriptions->first();
        if ($activeSub) {
            $this->editPlanSlug = $activeSub->plan_slug;
            $this->editDurationType = 'custom';
            $this->editExpiresAt = Carbon::parse($activeSub->expires_at)->format('Y-m-d');
        } else {
            $this->editPlanSlug = 'free';
            $this->editDurationType = '30';
            $this->editExpiresAt = now()->addDays(30)->format('Y-m-d');
        }

        $this->showUserModal = true;
    }

    public function closeUserModal()
    {
        $this->showUserModal = false;
        $this->reset([
            'selectedUserId', 'editName', 'editEmail', 'editIsAdmin',
            'editEmailVerified', 'editIsBanned', 'newPassword',
            'newPasswordConfirmation', 'generatedPassword',
            'editPlanSlug', 'editDurationType', 'editExpiresAt'
        ]);
        $this->resetValidation();
    }

    public function updatedEditDurationType($value)
    {
        if ($value === '30') {
            $this->editExpiresAt = now()->addDays(30)->format('Y-m-d');
        } elseif ($value === '90') {
            $this->editExpiresAt = now()->addDays(90)->format('Y-m-d');
        } elseif ($value === '180') {
            $this->editExpiresAt = now()->addDays(180)->format('Y-m-d');
        } elseif ($value === '365') {
            $this->editExpiresAt = now()->addDays(365)->format('Y-m-d');
        } elseif ($value === 'lifetime') {
            $this->editExpiresAt = now()->addYears(50)->format('Y-m-d');
        }
    }

    public function updatedEditPlanSlug($value)
    {
        if ($value !== 'free' && empty($this->editExpiresAt)) {
            $this->editExpiresAt = now()->addDays(30)->format('Y-m-d');
        }
    }

    public function generateRandomPassword()
    {
        $generated = Str::password(10, true, true, false);
        $this->newPassword = $generated;
        $this->newPasswordConfirmation = $generated;
        $this->generatedPassword = $generated;
        $this->toast('Password acak dibuat: ' . $generated, 'info');
    }

    public function verifyEmailNow()
    {
        $this->editEmailVerified = true;
        if ($this->selectedUserId) {
            User::where('id', $this->selectedUserId)->update(['email_verified_at' => now()]);
            $this->toast('Email pengguna telah diverifikasi.', 'success');
        }
    }

    public function saveUser()
    {
        if (!$this->selectedUserId) return;

        $user = User::findOrFail($this->selectedUserId);

        $rules = [
            'editName' => 'required|string|max:255',
            'editEmail' => 'required|email|max:255|unique:users,email,' . $user->id,
        ];

        if (!empty($this->newPassword)) {
            $rules['newPassword'] = 'required|min:6';
            $rules['newPasswordConfirmation'] = 'required|same:newPassword';
        }

        $this->validate($rules, [
            'editName.required' => 'Nama pengguna tidak boleh kosong.',
            'editEmail.required' => 'Email tidak boleh kosong.',
            'editEmail.unique' => 'Email sudah digunakan oleh akun lain.',
            'newPassword.min' => 'Password baru minimal 6 karakter.',
            'newPasswordConfirmation.same' => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Update basic info
        $user->name = $this->editName;
        $user->email = $this->editEmail;

        if ($user->id !== auth()->id()) {
            $user->is_admin = $this->editIsAdmin;
            $user->banned_at = $this->editIsBanned ? ($user->banned_at ?? now()) : null;
        }

        if ($this->editEmailVerified && !$user->email_verified_at) {
            $user->email_verified_at = now();
        }

        if (!empty($this->newPassword)) {
            $user->password = Hash::make($this->newPassword);
        }

        $user->save();

        // Handle subscription plan assignment
        if ($this->editPlanSlug === 'free') {
            // Cancel any active subscriptions
            Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->update(['status' => 'cancelled']);
        } else {
            $plan = Plan::where('slug', $this->editPlanSlug)->first();
            $planId = $plan ? $plan->id : null;

            // Calculate expires_at
            if ($this->editDurationType === '30') {
                $expiresAt = now()->addDays(30)->endOfDay();
            } elseif ($this->editDurationType === '90') {
                $expiresAt = now()->addDays(90)->endOfDay();
            } elseif ($this->editDurationType === '180') {
                $expiresAt = now()->addDays(180)->endOfDay();
            } elseif ($this->editDurationType === '365') {
                $expiresAt = now()->addDays(365)->endOfDay();
            } elseif ($this->editDurationType === 'lifetime') {
                $expiresAt = now()->addYears(50)->endOfDay();
            } elseif ($this->editDurationType === 'custom' && !empty($this->editExpiresAt)) {
                $expiresAt = Carbon::parse($this->editExpiresAt)->endOfDay();
            } else {
                $expiresAt = now()->addDays(30)->endOfDay();
            }

            // Expire old active subs
            Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            // Create new active sub directly from admin without payment
            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $planId,
                'plan_slug' => $this->editPlanSlug,
                'status' => 'active',
                'amount' => 0,
                'midtrans_order_id' => 'ADMIN-GRANT-' . $user->id . '-' . time(),
                'payment_type' => 'admin_grant',
                'starts_at' => now(),
                'expires_at' => $expiresAt,
            ]);
        }

        $this->toast('Pengaturan akun dan paket pengguna berhasil disimpan!', 'success');
        $this->closeUserModal();
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

    public function confirmDeleteUser($userId)
    {
        abort_if($userId === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');

        $user = User::find($userId);
        if (!$user) return;

        $this->confirmDialog(
            'Hapus akun ' . $user->name . '?',
            'Semua data pengguna, riwayat aktivitas, dan langganan akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.',
            'deleteUser',
            ['userId' => $userId]
        );
    }

    public function deleteUser($data)
    {
        $userId = $data['userId'] ?? null;
        if (!$userId) return;

        abort_if($userId === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');

        $user = User::find($userId);
        if ($user) {
            $user->delete();
            $this->toast('Pengguna telah berhasil dihapus permanen.', 'success');
            if ($this->selectedUserId === $userId) {
                $this->closeUserModal();
            }
        }
    }

    public function getListeners()
    {
        return [
            'banUser',
            'deleteUser',
        ];
    }

    public function render()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        $usersQuery = User::withSum(['subscriptions' => function($query) {
            $query->whereNotNull('midtrans_transaction_id');
        }], 'amount')
        ->with(['subscriptions' => function($query) {
            $query->where('status', 'active')->where('expires_at', '>', now())->with('plan')->orderBy('expires_at', 'desc');
        }]);

        if ($this->search) {
            $usersQuery->where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->planFilter === 'free') {
            $usersQuery->whereDoesntHave('subscriptions', function($query) {
                $query->where('status', 'active')->where('expires_at', '>', now());
            });
        } elseif ($this->planFilter !== 'all') {
            $usersQuery->whereHas('subscriptions', function($query) {
                $query->where('status', 'active')
                      ->where('expires_at', '>', now())
                      ->where('plan_slug', $this->planFilter);
            });
        }

        if ($this->statusFilter === 'banned') {
            $usersQuery->whereNotNull('banned_at');
        } elseif ($this->statusFilter === 'active') {
            $usersQuery->whereNull('banned_at');
        }

        $users = $usersQuery->latest()->paginate(10);

        return view('livewire.admin.users', compact('users', 'plans'));
    }
}
