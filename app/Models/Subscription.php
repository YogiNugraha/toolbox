<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id', 
        'plan_id',
        'plan_slug', 
        'status', 
        'starts_at', 
        'expires_at', 
        'midtrans_order_id', 
        'midtrans_transaction_id', 
        'payment_type',
        'amount',
        'snap_token'
    ];
    
    protected $casts = [
        'starts_at' => 'datetime', 
        'expires_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function activate()
    {
        // Idempotency: Jika langganan ini sudah aktif, abaikan
        if ($this->status === 'active') {
            return;
        }

        $plan = $this->plan ?? \App\Models\Plan::where('slug', $this->plan_slug)->first();
        $durationDays = $plan ? $plan->duration_days : 30; // fallback

        // Cek apakah ada langganan aktif lainnya yang belum expired (Stacking)
        $activeSub = Subscription::where('user_id', $this->user_id)
            ->where('status', 'active')
            ->where('id', '!=', $this->id)
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();

        $startsAt = now();
        $expiresAt = $durationDays ? now()->addDays($durationDays) : null;

        if ($activeSub) {
            $startsAt = $activeSub->expires_at ?? now();
            $expiresAt = $durationDays ? $startsAt->copy()->addDays($durationDays) : null;
            $activeSub->update(['status' => 'expired']);
        } else {
            Subscription::where('user_id', $this->user_id)
                ->where('status', 'active')
                ->where('id', '!=', $this->id)
                ->update(['status' => 'expired']);
        }
        
        $this->update([
            'status' => 'active',
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
        ]);

        \Illuminate\Support\Facades\Mail::to($this->user->email)->queue(new \App\Mail\PaymentSuccessMail($this));

        // Auto-close pending transactions
        Subscription::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->where('status', 'pending')
            ->update(['status' => 'failed']);
    }
}
