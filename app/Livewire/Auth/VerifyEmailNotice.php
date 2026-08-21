<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Traits\LivewireLineoneAlerts;

class VerifyEmailNotice extends Component
{
    use LivewireLineoneAlerts;

    public function checkVerification()
    {
        if (auth()->check() && auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }
    }

    public function resend()
    {
        if (auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $key = 'resend-verification:' . auth()->id();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            $this->toast("Tunggu $seconds detik sebelum mengirim ulang.", 'warning');
            return;
        }

        auth()->user()->sendEmailVerificationNotification();
        \Illuminate\Support\Facades\RateLimiter::hit($key, 60);

        $this->dispatch('cooldown-start', seconds: 60);
        $this->toast('Email verifikasi telah dikirim ulang.', 'success');
    }

    public function render()
    {
        return view('livewire.auth.verify-email-notice')->layout('layouts.base');
    }
}
