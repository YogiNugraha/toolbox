<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Traits\LivewireLineoneAlerts;

class Profile extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $photo;
    public $name;
    public $email;
    public $country_code = '+62';
    public $phone;

    public $current_password;
    public $password;
    public $password_confirmation;

    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:1024', // max 1MB
        ], [
            'photo.max' => 'Ukuran gambar terlalu besar. Maksimal 1MB.'
        ]);

        if ($this->photo) {
            // Delete old photo if exists
            if (auth()->user()->profile_photo_path) {
                Storage::disk('public')->delete(auth()->user()->profile_photo_path);
            }

            $path = $this->photo->store('profile-photos', 'public');
            auth()->user()->update(['profile_photo_path' => $path]);
            
            $this->dispatch('profile-photo-updated', path: Storage::url($path));
            $this->toast('Foto profil berhasil diperbarui.', 'success');
        }
    }

    public function mount()
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
        
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

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'country_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
        ]);

        $finalPhone = null;
        if (!empty($this->phone)) {
            $cleanPhone = ltrim(preg_replace('/[^0-9]/', '', $this->phone), '0');
            $finalPhone = $this->country_code . $cleanPhone;
        }

        $user = auth()->user();
        $isEmailChanged = $this->email !== $user->email;

        $user->update([
            'name' => $this->name,
            'phone' => $finalPhone,
        ]);

        if ($isEmailChanged) {
            $this->validate([
                'email' => ['required', 'email:rfc,dns', 'unique:users,email', 'unique:users,pending_email'],
            ]);

            $user->update(['pending_email' => $this->email]);
            $this->sendPendingEmailConfirmation($user);

            $this->toast("Link konfirmasi dikirim ke {$this->email}. Email akun belum berubah sampai link diklik.", 'info');

            $this->email = $user->email; // Revert the form UI to the current active email
        } else {
            $this->dispatch('profile-updated', name: $this->name);
            $this->toast('Profil berhasil diperbarui.', 'success');
        }
    }

    protected function sendPendingEmailConfirmation($user)
    {
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'profile.confirm-email',
            now()->addMinutes(60),
            ['user' => $user->id, 'hash' => sha1($user->pending_email)]
        );

        \Illuminate\Support\Facades\Mail::to($user->pending_email)->send(new \App\Mail\ConfirmNewEmailMail($user, $url));
    }

    public function cancelPendingEmail()
    {
        auth()->user()->update(['pending_email' => null]);
        $this->toast('Perubahan email dibatalkan.', 'info');
    }

    public function resendPendingEmail()
    {
        $key = 'resend-pending-email:' . auth()->id();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            $this->toast("Tunggu $seconds detik sebelum mengirim ulang.", 'warning');
            return;
        }

        $this->sendPendingEmailConfirmation(auth()->user());
        \Illuminate\Support\Facades\RateLimiter::hit($key, 60);

        $this->dispatch('cooldown-start', seconds: 60);
        $this->toast('Link konfirmasi telah dikirim ulang.', 'success');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        auth()->user()->update([
            'password' => \Illuminate\Support\Facades\Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->toast('Password berhasil diperbarui.', 'success');
    }

    public function render()
    {
        return view('livewire.dashboard.profile')->layout('layouts.dashboard');
    }
}
