<?php

namespace App\Traits;

trait LivewireLineoneAlerts
{
    /**
     * Show a Lineone toast notification
     * 
     * @param string $text The message to show
     * @param string $variant primary, secondary, info, success, warning, error
     */
    public function toast($text, $variant = 'success')
    {
        $this->js('$notification({text:"' . addslashes($text) . '",variant:"' . $variant . '"})');
    }

    /**
     * Show a Lineone confirm modal
     * 
     * @param string $title The modal title
     * @param string $text The modal description
     * @param string $action The Livewire action (or global event) to dispatch on confirm
     * @param array $data Additional data to pass to the action
     */
    public function confirmDialog($title, $text, $action, $data = [])
    {
        $dataJson = json_encode($data);
        $escapedTitle = addslashes($title);
        $escapedText = addslashes($text);
        $this->js("Alpine.store('confirmModal').open('{$escapedTitle}','{$escapedText}','{$action}',{$dataJson})");
    }
}
