<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class Register extends Component
{
    public $name;
    public $email;
    public $password;
    public $password_confirmation;

    #[\Livewire\Attributes\Url]
    public $redirect = '';

    public bool $emailChecking = false;
    public bool $emailValid = false;

    public function updatedEmail($value)
    {
        $this->emailValid = false;
        $this->resetErrorBag('email');

        if (empty($value)) return;

        $this->validateOnly('email', [
            'email' => 'required|email:rfc,dns|unique:users,email',
        ], [
            'email.email' => 'Format email tidak valid atau domain tidak ditemukan.',
            'email.unique' => 'Email ini sudah terdaftar. Sudah punya akun?',
        ]);

        $this->emailValid = ! $this->getErrorBag()->has('email');
    }

    public function register()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = \App\Models\User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);

        event(new \Illuminate\Auth\Events\Registered($user));

        \Illuminate\Support\Facades\Auth::login($user);
        
        $target = $this->redirect ?: session()->pull('url.intended', route('dashboard'));
        return redirect()->to($target);
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('layouts.base');
    }
}
