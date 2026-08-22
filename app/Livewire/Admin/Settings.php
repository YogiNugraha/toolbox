<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Setting as SettingModel;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;

class Settings extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    // Branding & Identitas
    public $site_name;
    public $site_tagline;
    public $site_description;
    
    // Logo & Favicon
    public $logo;
    public $existing_logo;
    public $favicon;
    public $existing_favicon;

    // Kontak & Bantuan
    public $support_email;
    public $support_whatsapp;

    // Footer
    public $footer_copyright;

    // Announcement Bar
    public $announcement_enabled = false;
    public $announcement_text;
    public $announcement_type = 'primary';

    public function mount()
    {
        $this->site_name = SettingModel::get('site_name', SettingModel::get('brand_name', config('app.name')));
        $this->site_tagline = SettingModel::get('site_tagline', SettingModel::get('brand_tagline', 'Platform Konversi & Optimasi Dokumen Digital'));
        $this->site_description = SettingModel::get('site_description', 'Solusi perkakas digital instan untuk mengolah, mengompres, dan mengonversi file Anda setiap hari tanpa instalasi software.');
        
        $this->existing_logo = SettingModel::get('site_logo');
        $this->existing_favicon = SettingModel::get('site_favicon');

        $this->support_email = SettingModel::get('support_email', 'support@mudahkerja.com');
        $this->support_whatsapp = SettingModel::get('support_whatsapp', '+6281234567890');

        $this->footer_copyright = SettingModel::get('footer_copyright', '© ' . date('Y') . ' ' . $this->site_name . '. All rights reserved.');

        $this->announcement_enabled = filter_var(SettingModel::get('announcement_enabled', false), FILTER_VALIDATE_BOOLEAN);
        $this->announcement_text = SettingModel::get('announcement_text', 'Dapatkan Diskon 20% Paket Pro untuk akses kuota pemrosesan tanpa batas!');
        $this->announcement_type = SettingModel::get('announcement_type', 'primary');
    }

    public function removeLogo()
    {
        if ($this->existing_logo && Storage::disk('public')->exists($this->existing_logo)) {
            Storage::disk('public')->delete($this->existing_logo);
        }
        $this->existing_logo = null;
        $this->logo = null;
        SettingModel::set('site_logo', null);
        $this->toast('Logo website berhasil dihapus!', 'info');
    }

    public function removeFavicon()
    {
        if ($this->existing_favicon && Storage::disk('public')->exists($this->existing_favicon)) {
            Storage::disk('public')->delete($this->existing_favicon);
        }
        $this->existing_favicon = null;
        $this->favicon = null;
        SettingModel::set('site_favicon', null);
        $this->toast('Favicon website berhasil dihapus!', 'info');
    }

    public function save()
    {
        $this->validate([
            'site_name' => 'required|string|max:100',
            'site_tagline' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'logo' => 'nullable|image|max:2048', // Maks 2MB
            'favicon' => 'nullable|image|mimes:png,ico,jpg,svg|max:1024', // Maks 1MB
            'support_email' => 'nullable|email|max:100',
            'support_whatsapp' => 'nullable|string|max:30',
            'footer_copyright' => 'nullable|string|max:255',
            'announcement_enabled' => 'boolean',
            'announcement_text' => 'nullable|string|max:255',
            'announcement_type' => 'required|in:primary,info,warning,success',
        ]);

        // Handle Logo Upload
        if ($this->logo) {
            if ($this->existing_logo && Storage::disk('public')->exists($this->existing_logo)) {
                Storage::disk('public')->delete($this->existing_logo);
            }
            $logoPath = $this->logo->store('settings', 'public');
            $this->existing_logo = $logoPath;
            SettingModel::set('site_logo', $logoPath);
            $this->logo = null;
        }

        // Handle Favicon Upload
        if ($this->favicon) {
            if ($this->existing_favicon && Storage::disk('public')->exists($this->existing_favicon)) {
                Storage::disk('public')->delete($this->existing_favicon);
            }
            $faviconPath = $this->favicon->store('settings', 'public');
            $this->existing_favicon = $faviconPath;
            SettingModel::set('site_favicon', $faviconPath);
            $this->favicon = null;
        }

        // Simpan Konfigurasi Teks
        SettingModel::set('site_name', $this->site_name);
        SettingModel::set('brand_name', $this->site_name); // Backward compatibility
        SettingModel::set('site_tagline', $this->site_tagline);
        SettingModel::set('brand_tagline', $this->site_tagline);
        SettingModel::set('site_description', $this->site_description);

        SettingModel::set('support_email', $this->support_email);
        SettingModel::set('support_whatsapp', $this->support_whatsapp);

        SettingModel::set('footer_copyright', $this->footer_copyright);

        SettingModel::set('announcement_enabled', $this->announcement_enabled ? '1' : '0');
        SettingModel::set('announcement_text', $this->announcement_text);
        SettingModel::set('announcement_type', $this->announcement_type);

        $this->toast('Pengaturan website berhasil disimpan!', 'success');
    }

    public function render()
    {
        return view('livewire.admin.settings')->layout('layouts.admin');
    }
}
