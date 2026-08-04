<?php

return [
    'free' => [
        'name' => 'Free',
        'price' => 0,
        'limits' => [
            'compress-image' => [
                'daily_quota' => 5,
                'locked_features' => ['preset_custom'],
            ],
            'convert-image' => [
                'daily_quota' => 5,
                'locked_features' => [],
            ],
            'pdf-to-word' => [
                'daily_quota' => 2,
                'max_file_size_mb' => 5,
                'locked_features' => [],
            ],
        ],
    ],
    'pro' => [
        'name' => 'Pro',
        'price' => 49000,
        'duration_days' => 30,
        'midtrans_item_name' => 'ToolBox Pro - 30 Hari',
        'limits' => [
            'compress-image' => [
                'daily_quota' => null, // unlimited
                'locked_features' => [],
            ],
            'convert-image' => [
                'daily_quota' => null,
                'locked_features' => [],
            ],
            'pdf-to-word' => [
                'daily_quota' => null,
                'max_file_size_mb' => 50,
                'locked_features' => [],
            ],
        ],
    ],
];
