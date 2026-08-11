<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Setting;

class PriceCalculator
{
    public function breakdown(Plan $plan): array
    {
        $basePrice = $plan->price;

        $discount = match ($plan->discount_type) {
            'percent' => (int) round($basePrice * $plan->discount_value / 100),
            'fixed' => min($plan->discount_value, $basePrice),
            default => 0,
        };

        $subtotal = $basePrice - $discount;

        $isServiceFeeEnabled = filter_var(Setting::get('is_service_fee_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $feeType = Setting::get('service_fee_type', 'fixed');
        $feeValue = (int) Setting::get('service_fee_value', 0);
        
        $isTaxEnabled = filter_var(Setting::get('is_tax_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $taxPercent = (int) Setting::get('tax_percent', 0);
        
        // service fee is not applied if basePrice is 0 (free plan)
        if ($basePrice == 0) {
            $serviceFee = 0;
            $tax = 0;
            $total = 0;
        } else {
            if ($isServiceFeeEnabled) {
                $serviceFee = $feeType === 'percent'
                    ? (int) round($subtotal * $feeValue / 100)
                    : $feeValue;
            } else {
                $serviceFee = 0;
            }

            if ($isTaxEnabled) {
                $tax = (int) round(($subtotal + $serviceFee) * $taxPercent / 100);
            } else {
                $tax = 0;
            }

            $total = $subtotal + $serviceFee + $tax;
        }

        return compact('basePrice', 'discount', 'subtotal', 'serviceFee', 'tax', 'taxPercent', 'total');
    }
}
