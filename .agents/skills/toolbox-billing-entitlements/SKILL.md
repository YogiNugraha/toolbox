---
name: toolbox-billing-entitlements
description: >-
  Use this skill when configuring pricing plans, monthly or daily tool quotas,
  lockable Pro features, user entitlements, or Midtrans payment gateway integrations in ToolBox.
---

# 💳 Toolbox Billing, Entitlements & Subscription Runbook

Skill ini digunakan untuk mengelola paket langganan (Plans), hak akses pengguna (Entitlements), batas kuota alat, dan transaksi pembayaran Midtrans pada proyek ToolBox.

## 1. Arsitektur Paket & Entitlement

ToolBox memisahkan hak akses ke dalam beberapa layer:
- **`App\Models\Plan`**: Menyimpan daftar paket (`free`, `pro`, `pro-max`) beserta kolom JSON `limits`.
- **`App\Services\EntitlementService`**: Service sentral untuk mengecek:
  1. `getRemainingQuota($user, $toolSlug)`: Sisa kuota pemakaian alat bulan ini berdasarkan tabel `activities`.
  2. `isFeatureLocked($user, $toolSlug, $featureKey)`: Pengecekan apakah opsi fitur tertentu terkunci untuk paket pengguna.
  3. `canProcessFile($user, $toolSlug, $fileSizeBytes)`: Pengecekan ukuran berkas maksimal (`max_file_size_mb`).

### Hak Akses Khusus Administrator:
Pengguna dengan `is_admin = true` secara otomatis:
- Mendapatkan kuota tidak terbatas (unlimited) pada seluruh alat.
- Membuka semua `locked_features`.
- Melewati batas ukuran berkas maksimum.

---

## 2. Struktur Limit pada Konfigurasi Paket

Format JSON kolom `limits` pada tabel `plans` (dan seeder `PlanSeeder.php`):
```json
{
    "compress-image": {
        "monthly_quota": 5,
        "max_file_size_mb": 10,
        "locked_features": ["preset_custom"]
    },
    "pdf-to-word": {
        "monthly_quota": null,
        "max_file_size_mb": 50,
        "locked_features": []
    }
}
```
*Catatan:* `monthly_quota: null` menandakan kuota tak terbatas (unlimited).

---

## 3. Alur Pembayaran Midtrans Snap

1. **Pembuatan Tagihan**:
   - Komponen `App\Livewire\Pricing` menghitung harga akhir melalui `App\Services\PriceCalculator`.
   - Membuat rekaman `App\Models\Subscription` dengan status `pending`.
   - Meminta Snap Token ke API Midtrans via SDK `midtrans/midtrans-php`.
   - Menampilkan modal popup Snap pada sisi klien.

2. **Webhook Listener (`/webhook/midtrans`)**:
   - Ditangani oleh `App\Http\Controllers\MidtransWebhookController`.
   - Rute ini dikecualikan dari verifikasi CSRF di `bootstrap/app.php`.
   - Memverifikasi `signature_key` dari Midtrans.
   - Mengubah status langganan menjadi `active` dan menyetel `expires_at` (misal +30 hari untuk paket bulanan).

---

## 4. Perintah Berguna Terkait Billing

### Re-seed Paket Langganan:
```powershell
php artisan db:seed --class=PlanSeeder
```

### Memberikan Status Admin ke Pengguna:
```powershell
php artisan tinker --execute="App\Models\User::where('email', 'admin@example.com')->update(['is_admin' => true]);"
```
