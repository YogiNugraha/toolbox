<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class Profile extends Component
{
    use WithFileUploads;

    public $photo;
    public $name;
    public $email;

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
            session()->flash('profile_message', 'Foto profil berhasil diperbarui!');
        }
    }

    public function mount()
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
        ]);

        auth()->user()->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->dispatch('profile-updated', name: $this->name);
        session()->flash('profile_message', 'Profil berhasil diperbarui!');
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

        session()->flash('password_message', 'Password berhasil diperbarui!');
    }

    public function render()
    {
        return view('livewire.dashboard.profile')->layout('layouts.dashboard');
    }
}
