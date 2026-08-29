<?php

return [
    'free' => [
        'name' => 'Free',
        'price' => 0,
        'limits' => [
            'compress-image' => [
                'monthly_quota' => 5,
                'locked_features' => ['preset_custom'],
            ],
            'convert-image' => [
                'monthly_quota' => 5,
                'locked_features' => [],
            ],
            'pdf-to-word' => [
                'monthly_quota' => 5,
                'max_file_size_mb' => 10,
                'locked_features' => [],
            ],
            'compress-pdf' => [
                'monthly_quota' => 5,
                'max_file_size_mb' => 20,
                'locked_features' => ['preset_custom'],
            ],
            'word-to-pdf' => [
                'monthly_quota' => 5,
                'max_file_size_mb' => 20,
                'locked_features' => [],
            ],
            'pdf-to-powerpoint' => [
                'monthly_quota' => 5,
                'max_file_size_mb' => 20,
                'locked_features' => [],
            ],
            'powerpoint-to-pdf' => [
                'monthly_quota' => 5,
                'max_file_size_mb' => 20,
                'locked_features' => [],
            ],
            'pdf-to-excel' => [
                'monthly_quota' => 5,
                'max_file_size_mb' => 20,
                'locked_features' => [],
            ],
            'excel-to-pdf' => [
                'monthly_quota' => 5,
                'max_file_size_mb' => 20,
                'locked_features' => [],
            ],
            'merge-pdf' => [
                'monthly_quota' => 5,
                'max_file_size_mb' => 20,
                'locked_features' => [],
            ],
            'split-pdf' => [
                'monthly_quota' => 5,
                'max_file_size_mb' => 20,
                'locked_features' => [],
            ],
            'pdf-to-jpg' => [
                'monthly_quota' => 5,
                'max_file_size_mb' => 20,
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
                'monthly_quota' => null, // unlimited
                'locked_features' => [],
            ],
            'convert-image' => [
                'monthly_quota' => null,
                'locked_features' => [],
            ],
            'pdf-to-word' => [
                'monthly_quota' => null,
                'max_file_size_mb' => 50,
                'locked_features' => [],
            ],
            'compress-pdf' => [
                'monthly_quota' => null,
                'max_file_size_mb' => 50,
                'locked_features' => [],
            ],
            'word-to-pdf' => [
                'monthly_quota' => null,
                'max_file_size_mb' => 50,
                'locked_features' => [],
            ],
            'pdf-to-powerpoint' => [
                'monthly_quota' => null,
                'max_file_size_mb' => 50,
                'locked_features' => [],
            ],
            'powerpoint-to-pdf' => [
                'monthly_quota' => null,
                'max_file_size_mb' => 50,
                'locked_features' => [],
            ],
            'pdf-to-excel' => [
                'monthly_quota' => null,
                'max_file_size_mb' => 50,
                'locked_features' => [],
            ],
            'excel-to-pdf' => [
                'monthly_quota' => null,
                'max_file_size_mb' => 50,
                'locked_features' => [],
            ],
            'merge-pdf' => [
                'monthly_quota' => null,
                'max_file_size_mb' => 50,
                'locked_features' => [],
            ],
            'split-pdf' => [
                'monthly_quota' => null,
                'max_file_size_mb' => 50,
                'locked_features' => [],
            ],
            'pdf-to-jpg' => [
                'monthly_quota' => null,
                'max_file_size_mb' => 50,
                'locked_features' => [],
            ],
        ],
    ],
];
