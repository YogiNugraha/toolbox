<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Traits\LivewireLineoneAlerts;

class ForgotPassword extends Component
{
    use LivewireLineoneAlerts;

    public $email;

    public function sendResetLink()
    {
        $this->validate(['email' => 'required|email']);

        $key = 'send-reset-link:' . strtolower($this->email);
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            $this->addError('email', "Tunggu $seconds detik sebelum mengirim ulang.");
            return;
        }

        $status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => $this->email]);

        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            \Illuminate\Support\Facades\RateLimiter::hit($key, 60);
            $this->dispatch('cooldown-start', seconds: 60);
            $this->toast('Link reset password sudah dikirim ke email kamu.', 'success');
        } else {
            $this->addError('email', 'Email tidak ditemukan.');
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')->layout('layouts.base');
    }
}
