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
        $textJson = json_encode((string) $text);
        $variantJson = json_encode((string) $variant);
        $this->js('$notification({text:' . $textJson . ',variant:' . $variantJson . '})');
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
        $titleJson = json_encode((string) $title);
        $textJson = json_encode((string) $text);
        $actionJson = json_encode((string) $action);
        $dataJson = json_encode($data);
        $this->js("Alpine.store('confirmModal').open({$titleJson},{$textJson},{$actionJson},{$dataJson})");
    }
}
