<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subscription;
use Midtrans\Config;
use Midtrans\Transaction;

Config::$serverKey = config('services.midtrans.server_key');
Config::$isProduction = config('services.midtrans.is_production');

$subs = Subscription::whereNull('midtrans_transaction_id')->whereIn('status', ['active', 'expired'])->get();
foreach($subs as $sub) {
    try {
        $status = (array) Transaction::status($sub->midtrans_order_id);
        if (isset($status['transaction_id'])) {
            $sub->update(['midtrans_transaction_id' => $status['transaction_id']]);
            echo 'Updated ' . $sub->midtrans_order_id . ' with ' . $status['transaction_id'] . PHP_EOL;
        }
    } catch (\Exception $e) {
        echo 'Failed ' . $sub->midtrans_order_id . ': ' . $e->getMessage() . PHP_EOL;
    }
}
