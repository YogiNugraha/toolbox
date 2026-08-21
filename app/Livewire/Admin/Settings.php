<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Setting as SettingModel;
use App\Traits\LivewireLineoneAlerts;

class Settings extends Component
{
    use LivewireLineoneAlerts;

    public $brand_name;
    public $brand_tagline;
    public $support_email;
    
    public function mount()
    {
        $this->brand_name = SettingModel::get('brand_name', config('app.name'));
        $this->brand_tagline = SettingModel::get('brand_tagline', 'Platform Konversi & Optimasi Dokumen Digital');
        $this->support_email = SettingModel::get('support_email', 'support@mudahkerja.com');
    }

    public function save()
    {
        $this->validate([
            'brand_name' => 'required|string|max:255',
            'brand_tagline' => 'nullable|string|max:255',
            'support_email' => 'nullable|email|max:255',
        ]);

        SettingModel::set('brand_name', $this->brand_name);
        SettingModel::set('brand_tagline', $this->brand_tagline);
        SettingModel::set('support_email', $this->support_email);

        $this->toast('Pengaturan website berhasil disimpan!', 'success');
    }

    public function render()
    {
        return view('livewire.admin.settings')->layout('layouts.admin');
    }
}
