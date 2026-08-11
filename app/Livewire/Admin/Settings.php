<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Setting as SettingModel;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Settings extends Component
{
    public $brand_name;
    
    public function mount()
    {
        $this->brand_name = SettingModel::get('brand_name', config('app.name'));
    }

    public function save()
    {
        $this->validate([
            'brand_name' => 'required|string|max:255',
        ]);

        SettingModel::set('brand_name', $this->brand_name);

        LivewireAlert::title('Pengaturan global berhasil disimpan!')->success()->toast()->position('top-end')->timer(3000)->show();
    }

    public function render()
    {
        return view('livewire.admin.settings')->layout('layouts.admin');
    }
}
