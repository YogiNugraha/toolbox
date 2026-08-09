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
            $transactionId = $notification->transaction_id ?? null;

            $subscription = Subscription::where('midtrans_order_id', $orderId)->first();

            if (!$subscription) {
                return response()->json(['message' => 'Subscription not found'], 404);
            }

            if ($transactionId && empty($subscription->midtrans_transaction_id)) {
                $subscription->update(['midtrans_transaction_id' => $transactionId]);
            }

            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $subscription->update(['status' => 'pending']);
                    } else {
                        $subscription->activate();
                    }
                }
            } else if ($transaction == 'settlement') {
                $subscription->activate();
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

}
