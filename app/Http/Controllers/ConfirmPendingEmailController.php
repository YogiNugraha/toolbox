<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfirmPendingEmailController extends Controller
{
    public function __invoke(\Illuminate\Http\Request $request, \App\Models\User $user)
    {
        abort_unless($user->id === auth()->id(), 403);
        abort_if(! $user->pending_email, 404, 'Tidak ada perubahan email yang menunggu konfirmasi.');
        abort_unless(hash_equals(sha1($user->pending_email), (string) $request->route('hash')), 403);

        $user->forceFill([
            'email' => $user->pending_email,
            'pending_email' => null,
            'email_verified_at' => now(), // langsung terverifikasi
        ])->save();

        return redirect()->route('email.verified');
    }
}
