<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Setup Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');

        try {
            $notification = new Notification();
            
            $payload = $request->all();
            
            // Verifikasi signature
            $signatureKey = $payload['signature_key'] ?? '';
            $orderIdRaw = $payload['order_id'] ?? '';
            $statusCodeRaw = $payload['status_code'] ?? '';
            $grossAmountRaw = $payload['gross_amount'] ?? '';
            $serverKey = config('services.midtrans.server_key');
            
            $signature = hash('sha512', $orderIdRaw . $statusCodeRaw . $grossAmountRaw . $serverKey);
            
            if ($signature !== $signatureKey) {
                \Log::warning('Midtrans Webhook: Invalid signature', ['order_id' => $orderIdRaw]);
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            $transaction = $notification->transaction_status;
            $type = $notification->payment_type;
            $orderId = $notification->order_id;
            $fraud = $notification->fraud_status;

            $subscription = Subscription::where('midtrans_order_id', $orderId)->first();

            if (!$subscription) {
                return response()->json(['message' => 'Subscription not found'], 404);
            }

            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $subscription->update(['status' => 'pending']);
                    } else {
                        $this->activateSubscription($subscription);
                    }
                }
            } else if ($transaction == 'settlement') {
                $this->activateSubscription($subscription);
            } else if ($transaction == 'pending') {
                $subscription->update(['status' => 'pending']);
            } else if ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
                $subscription->update(['status' => 'failed']);
            }

            return response()->json(['message' => 'Notification processed successfully']);
        } catch (\Exception $e) {
            \Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Server Error'], 500);
        }
    }

    private function activateSubscription(Subscription $subscription)
    {
        // Idempotency: Jika langganan ini sudah aktif, abaikan agar expires_at tidak bertambah berkali-kali
        if ($subscription->status === 'active') {
            return;
        }

        $durationDays = config('plans.' . $subscription->plan_slug . '.duration_days');
        
        // Cek apakah ada langganan aktif lainnya yang belum expired (E1 Upgrade Stacking)
        $activeSub = Subscription::where('user_id', $subscription->user_id)
            ->where('status', 'active')
            ->where('id', '!=', $subscription->id)
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();

        $startsAt = now();
        $expiresAt = now()->addDays($durationDays);

        if ($activeSub) {
            // Jika ada langganan lama yang masih aktif, masa aktif langganan baru ditambahkan ke akhir masa aktif lama
            $startsAt = $activeSub->expires_at;
            $expiresAt = $activeSub->expires_at->addDays($durationDays);
            
            // Tandai langganan lama sebagai expired karena sudah digantikan oleh langganan baru ini
            $activeSub->update(['status' => 'expired']);
        } else {
            // Cancel any old active that might be expired but still have status=active in DB
            Subscription::where('user_id', $subscription->user_id)
                ->where('status', 'active')
                ->where('id', '!=', $subscription->id)
                ->update(['status' => 'expired']);
        }
        
        $subscription->update([
            'status' => 'active',
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
        ]);

        \Illuminate\Support\Facades\Mail::to($subscription->user->email)->queue(new \App\Mail\PaymentSuccessMail($subscription));

        // Fix A: Auto-close pending transactions
        Subscription::where('user_id', $subscription->user_id)
            ->where('id', '!=', $subscription->id)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);
    }
}
