<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Traits\LivewireLineoneAlerts;

class Login extends Component
{
    use LivewireLineoneAlerts;

    public $email;
    public $password;
    public $remember = false;

    public function mount()
    {
        if (session()->has('error')) {
            $this->toast(session('error'), 'error');
        }
    }

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $key = 'login:' . request()->ip() . ':' . strtolower($this->email);
        
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            $this->addError('email', "Terlalu banyak percobaan. Silakan coba lagi dalam $seconds detik.");
            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            \Illuminate\Support\Facades\RateLimiter::hit($key, 60); // block for 1 minute after 5 failed attempts
            $this->addError('email', 'Email atau password salah.');
            return;
        }

        \Illuminate\Support\Facades\RateLimiter::clear($key);

        if (auth()->user()->banned_at) {
            Auth::logout();
            $this->addError('email', 'Akun kamu telah dinonaktifkan.');
            return;
        }

        $token = \Illuminate\Support\Str::random(60);
        auth()->user()->update(['current_session_token' => $token]);
        session(['session_token' => $token]);

        request()->session()->regenerate();
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.base');
    }
}
