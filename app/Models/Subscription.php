<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id', 
        'plan_slug', 
        'status', 
        'starts_at', 
        'expires_at', 
        'midtrans_order_id', 
        'midtrans_transaction_id', 
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

    public function activate()
    {
        // Idempotency: Jika langganan ini sudah aktif, abaikan
        if ($this->status === 'active') {
            return;
        }

        $durationDays = config('plans.' . $this->plan_slug . '.duration_days');
        
        // Cek apakah ada langganan aktif lainnya yang belum expired (Stacking)
        $activeSub = Subscription::where('user_id', $this->user_id)
            ->where('status', 'active')
            ->where('id', '!=', $this->id)
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();

        $startsAt = now();
        $expiresAt = now()->addDays($durationDays);

        if ($activeSub) {
            $startsAt = $activeSub->expires_at;
            $expiresAt = $activeSub->expires_at->addDays($durationDays);
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
            ->update(['status' => 'expired']);
    }
}
