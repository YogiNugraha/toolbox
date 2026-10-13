# Architecture & System Rules: ToolBox

Dokumen ini mendefinisikan arsitektur sistem, struktur direktori, dan aturan teknis operasional pada proyek ToolBox.

## 1. Lingkungan Eksekusi & Konfigurasi
- **Web Server**: Laragon (Apache/Nginx virtual host di `http://toolbox.test`).
- **Database**: SQLite default (`database/database.sqlite`). Pastikan migrasi selalu sinkron dengan skema.
- **Queue Connection**: Driver `database`. Fitur konversi asinkron (misalnya PDF ke Word) membutuhkan worker aktif (`php artisan queue:work` atau `php artisan queue:listen`).
- **File Retention**: Berkas sementara disimpan di `storage/app/private/users/{userId}/`. Pembersihan berkas kedaluwarsa dijalankan oleh perintah `php artisan cleanup:temp` setiap jam (retensi default 24 jam via `FILE_RETENTION_HOURS`).

## 2. Struktur Direktori Utama
```text
c:\laragon\www\toolbox\
├── app/
│   ├── Console/Commands/       # Perintah terjadwal (CleanupTempFiles)
│   ├── Http/
│   │   ├── Controllers/        # MidtransWebhookController, Auth verifications
│   │   └── Middleware/         # EnsureSingleSession, EnsureUserIsNotBanned, IsAdmin
│   ├── Jobs/                   # Asynchronous background jobs (ConvertPdfToWordJob)
│   ├── Livewire/
│   │   ├── Admin/              # Modul administrasi (Overview, Plans, Users, Transactions)
│   │   ├── Auth/               # Autentikasi (Login, Register, Reset, VerifyEmail)
│   │   ├── Dashboard/          # Modul pengguna (Overview, Billing, History, Profile)
│   │   └── Tools/              # Komponen Livewire untuk masing-masing tool
│   ├── Models/                 # Eloquent Models (Tool, Plan, Subscription, Activity, User)
│   ├── Services/
│   │   ├── EntitlementService.php  # Kuota, limits, dan pengecekan akses paket
│   │   ├── PriceCalculator.php     # Perhitungan diskon dan total tagihan
│   │   └── Tools/                  # Mesin logika setiap alat (14+ service)
│   └── Traits/                 # LivewireLineoneAlerts
├── config/
│   ├── plans.php               # Konfigurasi paket & limit default
│   ├── tools.php               # Konfigurasi katalog seluruh tool
│   └── services.php            # Konfigurasi Python path & Midtrans API credentials
├── resources/
│   └── views/
│       ├── layouts/            # dashboard.blade.php, tool.blade.php, admin.blade.php
│       └── livewire/           # Template Blade komponen Livewire
├── storage/
│   ├── app/private/            # Penyimpanan berkas hasil olahan pengguna
│   └── scripts/                # Kumpulan skrip CLI Python untuk pengolahan berkas
└── routes/
    └── web.php                 # Rute web, tool wrapper, download handler, dan admin
```

## 3. Eksekusi Python & Mesin Pihak Ketiga
- Path interpreter Python dibaca dari `config('services.python_path')`, dengan default `python`.
- Skrip Python di `storage/scripts/` dijalankan melalui `Symfony\Component\Process\Process`.
- Wajib menggunakan timeout eksplisit (misal 60 detik untuk thumbnail/metadata, 120–300 detik untuk konversi berat).
- Komunikasi antara PHP dan Python dilakukan melalui argumen CLI dan pertukaran JSON via `stdout` / `stderr`.
- LibreOffice dijalankan secara headless dengan parameter isolasi profil `-env:UserInstallation=file:///...` untuk menghindari konflik konkurensi pada lingkungan multi-user.
