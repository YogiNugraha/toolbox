<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plansConfig = config('plans');

        if (empty($plansConfig)) {
            // Fallback just in case config is removed early
            $plansConfig = [
                'free' => [
                    'name' => 'Free',
                    'price' => 0,
                    'limits' => [
                        'compress-image' => ['daily_quota' => 5, 'locked_features' => ['preset_custom']],
                        'convert-image' => ['daily_quota' => 5, 'locked_features' => []],
                        'pdf-to-word' => ['daily_quota' => 2, 'max_file_size_mb' => 5, 'locked_features' => []],
                    ],
                ],
                'pro' => [
                    'name' => 'Pro',
                    'price' => 49000,
                    'duration_days' => 30,
                    'limits' => [
                        'compress-image' => ['daily_quota' => null, 'locked_features' => []],
                        'convert-image' => ['daily_quota' => null, 'locked_features' => []],
                        'pdf-to-word' => ['daily_quota' => null, 'max_file_size_mb' => 50, 'locked_features' => []],
                    ],
                ]
            ];
        }

        \App\Models\Plan::firstOrCreate(['slug' => 'free'], [
            'name' => $plansConfig['free']['name'],
            'price' => $plansConfig['free']['price'],
            'duration_days' => null,
            'description' => 'Paket dasar dengan fitur standar.',
            'limits' => $plansConfig['free']['limits'],
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        \App\Models\Plan::firstOrCreate(['slug' => 'pro'], [
            'name' => $plansConfig['pro']['name'],
            'price' => $plansConfig['pro']['price'],
            'duration_days' => $plansConfig['pro']['duration_days'] ?? 30,
            'description' => 'Akses penuh tanpa batas harian untuk produktivitas maksimal.',
            'limits' => $plansConfig['pro']['limits'],
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);
    }
}
