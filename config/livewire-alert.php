<?php

/*
 * For more details about the configuration, see:
 * https://sweetalert2.github.io/#configuration
 */

use Jantinnerezo\LivewireAlert\Enums\Position;

return [
    'position' => Position::Center,
    'timer' => 3000,
    'toast' => false,
    'text' => null,
    'confirmButtonText' => 'Yes',
    'cancelButtonText' => 'Cancel',
    'denyButtonText' => 'No',
    'showCancelButton' => false,
    'showConfirmButton' => false,
    'backdrop' => true,
    'options' => [
        'buttonsStyling' => false,
        'customClass' => [
            'popup' => 'rounded-sm border border-hairline font-sans shadow-none',
            'title' => 'font-display font-bold text-ink text-lg',
            'htmlContainer' => 'text-ink-muted text-sm',
            'confirmButton' => 'bg-amber text-ink font-medium px-4 py-2 rounded-sm text-sm mx-1',
            'cancelButton' => 'border border-hairline text-ink-muted px-4 py-2 rounded-sm text-sm mx-1 bg-white',
        ],
    ],
];
