<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class VerifyEmailNotice extends Component
{
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
            \Jantinnerezo\LivewireAlert\Facades\LivewireAlert::title("Tunggu $seconds detik sebelum mengirim ulang.")->warning()->toast()->position('top-end')->show();
            return;
        }

        auth()->user()->sendEmailVerificationNotification();
        \Illuminate\Support\Facades\RateLimiter::hit($key, 60);

        $this->dispatch('cooldown-start', seconds: 60);
        \Jantinnerezo\LivewireAlert\Facades\LivewireAlert::title('Email verifikasi telah dikirim ulang.')->success()->toast()->position('top-end')->timer(3000)->show();
    }

    public function render()
    {
        return view('livewire.auth.verify-email-notice')->layout('layouts.base');
    }
}
