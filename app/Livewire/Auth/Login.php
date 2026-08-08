<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Login extends Component
{
    public $email;
    public $password;
    public $remember = false;

    public function mount()
    {
        if (session()->has('error')) {
            LivewireAlert::title(session('error'))->error()->show();
        }
    }

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'Email atau password salah.');
            return;
        }

        if (auth()->user()->banned_at) {
            Auth::logout();
            LivewireAlert::title('Akun kamu telah dinonaktifkan karena melanggar.')->error()->show();
            return;
        }

        request()->session()->regenerate();
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.base');
    }
}
