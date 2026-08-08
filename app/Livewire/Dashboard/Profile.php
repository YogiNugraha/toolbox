<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Profile extends Component
{
    use WithFileUploads;

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
            LivewireAlert::title('Foto profil berhasil diperbarui.')->success()->toast()->position('top-end')->timer(2500)->show();
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
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
            'country_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
        ]);

        $finalPhone = null;
        if (!empty($this->phone)) {
            $cleanPhone = ltrim(preg_replace('/[^0-9]/', '', $this->phone), '0');
            $finalPhone = $this->country_code . $cleanPhone;
        }

        auth()->user()->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $finalPhone,
        ]);

        $this->dispatch('profile-updated', name: $this->name);
        LivewireAlert::title('Profil berhasil diperbarui.')->success()->toast()->position('top-end')->timer(2500)->show();
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

        LivewireAlert::title('Password berhasil diperbarui.')->success()->toast()->position('top-end')->timer(2500)->show();
    }

    public function render()
    {
        return view('livewire.dashboard.profile')->layout('layouts.dashboard');
    }
}
