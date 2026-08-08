# Dokumentasi & Handover Proyek: ToolBox (Sistem Alat Produktivitas)

Dokumen ini disusun untuk memberikan gambaran komprehensif mengenai arsitektur, fitur, alur sistem, dan status terkini dari proyek **ToolBox** (berbasis Laravel 11 & Livewire 3). Dokumen ini sangat cocok digunakan sebagai rujukan bagi AI (seperti Claude) atau *developer* lain yang akan melanjutkan pengembangan.

---

## 1. Ringkasan Proyek
**ToolBox** adalah aplikasi berbasis web (SaaS) yang menawarkan berbagai alat produktivitas (misalnya: kompresi gambar, konversi PDF, dll). Sistem ini menggunakan model Freemium, di mana pengguna dapat mendaftar secara gratis (Free) dan meningkatkan (Upgrade) akun mereka ke paket **Pro** berbayar untuk menikmati fitur tanpa batasan.

### Stack Teknologi Utama:
- **Framework Utama:** Laravel 11
- **Frontend / Reaktivitas:** Livewire 3 + Alpine.js
- **Styling:** Tailwind CSS (dikustomisasi dengan warna spesifik: Ink, Paper, Amber)
- **Payment Gateway:** Midtrans (Snap Embed & Webhook)
- **PDF Generator:** barryvdh/laravel-dompdf
- **Antrean (Queue):** Database Queue (untuk pengiriman email otomatis)

---

## 2. Alur Sistem (System Flow)

### A. Alur Autentikasi
1. Pengguna mendaftar melalui halaman `/register` (komponen Livewire).
2. Setelah mendaftar, otomatis *login* dan diarahkan ke `/dashboard`.
3. Akun secara *default* memiliki status berlangganan **Free** (tidak memiliki langganan aktif di tabel `subscriptions`).

### B. Alur Berlangganan (Billing & Payment via Midtrans)
1. **Inisiasi (Checkout):** Pengguna masuk ke halaman `/pricing`, memilih paket Pro, lalu sistem membuat *Order ID* (format: `PRO-{user_id}-{timestamp}-{random}`) dan meminta **Snap Token** ke API Midtrans.
2. **Pembayaran:** Komponen Livewire memunculkan popup pembayaran Midtrans (menggunakan fungsi `snap.embed()` di dalam halaman tanpa *redirect*).
3. **Pengecekan (Polling):** Sembari menunggu, komponen frontend melakukan *polling* (setiap 10 detik) menggunakan `wire:poll.10s`. Polling ini bertugas mengecek status pembayaran ke database, atau melakukan *fallback* dengan mengecek langsung ke API Midtrans untuk menghindari delay dari Webhook.
4. **Webhook Konfirmasi:** Di latar belakang, Midtrans mengirimkan notifikasi ke `/webhook/midtrans`. Sistem akan memverifikasi *Signature Key*, lalu meng-update status `subscriptions` di database menjadi `active` (Lunas) serta menetapkan tanggal kedaluwarsa (`expires_at`) 30 hari ke depan.
5. **Notifikasi:** Setelah berhasil, sistem memasukkan perintah pengiriman email ke dalam antrean (Queue). Pengguna akan menerima email (Markdown) tanda terima pembayaran yang berisi lampiran (Attachment) file **PDF Invoice** berdesain elegan.

### C. Alur Pembatalan (Cancel)
1. Pengguna mengklik tombol "Berhenti Berlangganan" di `/billing`.
2. Sistem mengubah status langganan menjadi `expired` dan memajukan `expires_at` ke waktu saat ini (`now()`).
3. Sistem mengirimkan Email Konfirmasi Pembatalan kepada pengguna melalui Queue.

---

## 3. Struktur Folder Penting

```text
c:\laragon\www\toolbox\
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── MidtransWebhookController.php   # Menerima webhook dari Midtrans
│   ├── Livewire/
│   │   ├── Auth/                               # Komponen Login & Register
│   │   ├── Dashboard/                          
│   │   │   ├── Billing.php                     # Mengelola UI langganan, riwayat, batal
│   │   │   ├── History.php                     # Riwayat penggunaan tool pengguna
│   │   │   ├── Invoice.php                     # Menampilkan invoice HTML di web
│   │   │   ├── Overview.php                    # Dasbor utama
│   │   │   └── Profile.php                     # Edit profil pengguna
│   │   └── Pricing.php                         # Logika checkout Snap Midtrans
│   ├── Mail/
│   │   ├── PaymentSuccessMail.php              # Class pengirim email Lunas + PDF
│   │   └── SubscriptionCancelledMail.php       # Class pengirim email Pembatalan
│   └── Models/
│       ├── Subscription.php                    # Relasi dan helper (is_active, dll)
│       ├── User.php                            # Relasi user ke subscriptions & activities
│       └── Activity.php                        # Menyimpan log aktivitas tool
├── bootstrap/
│   └── app.php                                 # Konfigurasi middleware & pengecualian CSRF
├── config/
│   ├── app.php                                 # Konfigurasi APP_NAME & APP_LOGO
│   ├── plans.php                               # Definisi harga & durasi paket (Pro)
│   ├── services.php                            # Kredensial Midtrans
│   └── tools.php                               # Definisi daftar tools yang tersedia
├── resources/
│   └── views/
│       ├── emails/                             # Tampilan email Markdown
│       ├── layouts/                            # Layout utama (base, dashboard, tool)
│       ├── livewire/                           # UI komponen Livewire
│       └── pdf/
│           └── invoice.blade.php               # Template HTML murni (CSS Vanilla) untuk DOMPDF
├── routes/
│   └── web.php                                 # Definisi seluruh rute aplikasi
└── .env                                        # Kredensial, APP_NAME, APP_LOGO
```

---

## 4. Daftar Rute (Routes)

| Rute URL | Middleware | Keterangan |
| :--- | :--- | :--- |
| `/` | `web` | Halaman Landing Page (Welcome) |
| `/login` | `guest, throttle:5,1` | Halaman Login |
| `/register` | `guest, throttle:5,1` | Halaman Pendaftaran |
| `/webhook/midtrans` | `(Pengecualian CSRF)` | Menerima *callback* status transaksi dari server Midtrans |
| `/dashboard` | `auth, throttle:60,1` | Ringkasan dasbor pengguna |
| `/history` | `auth, throttle:60,1` | Riwayat aktivitas (penggunaan tools) |
| `/profile` | `auth, throttle:60,1` | Pengaturan profil pengguna |
| `/pricing` | `auth, throttle:60,1` | Halaman penawaran paket & inisiasi Midtrans Snap |
| `/billing` | `auth, throttle:60,1` | Dasbor status langganan, aksi perpanjang/batal, dan riwayat order |
| `/billing/invoice/{order_id}`| `auth, throttle:60,1` | Halaman tampilan Invoice via Web browser |
| `/tool/{slug}` | `auth, throttle:60,1` | Halaman eksekusi tool dinamis (diambil dari `config/tools.php`) |
| `/download/{activity}`| `auth, throttle:60,1` | Endpoint untuk mengunduh hasil file *output* dari tool |

*(Catatan: Limit `throttle` untuk halaman auth telah ditingkatkan menjadi 60/menit agar tidak bentrok dengan metode `wire:poll`)*

---

## 5. Fitur Utama yang Sudah Selesai
1. **Dynamic Branding:** Nama aplikasi dan Logo direferensikan dari `config('app.name')` dan `env('APP_LOGO')` di seluruh tampilan.
2. **Sistem Autentikasi:** Mendaftar, masuk, dan keluar (Livewire-based).
3. **Midtrans Snap Embed:** Pembayaran aman dan mulus di dalam *frame* halaman (tanpa melempar user ke tab baru).
4. **Robust Payment Verification:** Menggunakan dua lapis pengecekan (Webhook + Live API Polling) untuk mencegah transaksi menggantung akibat *delay* koneksi.
5. **PDF Invoice Automation:** Otomatisasi pembentukan dokumen PDF yang desainnya meniru (1:1) desain antarmuka HTML/Tailwind pada aplikasi menggunakan DOMPDF (dengan konversi kode ke CSS konvensional agar *compatible*).
6. **Queue Emailing:** Mengirim email sukses berlangganan & batal berlangganan di latar belakang agar *loading* aplikasi tetap super cepat.
7. **Billing Dashboard:** Antarmuka responsif yang jelas untuk memantau waktu jatuh tempo langganan, tombol perpanjangan, tombol membatalkan langganan, dan tabel riwayat transaksi.

---

## 6. Kekurangan (Tech Debt) & Ruang Peningkatan

Sistem saat ini sudah sangat mumpuni untuk standar MVP, namun ada beberapa hal yang dapat dikembangkan lebih lanjut oleh *developer* selanjutnya (AI):

1. **Refund / Chargeback Handling:** Webhook Midtrans (`MidtransWebhookController`) belum mengatur skenario ketika dana ditarik kembali (refund/chargeback). Saat ini hanya menangani status `settlement`, `capture`, `pending`, `deny`, `expire`, dan `cancel`.
2. **Cron Job (Task Scheduling):** Perlu ditambahkan perintah Command (`php artisan schedule:run`) yang secara harian membersihkan transaksi berstatus `pending` yang kedaluwarsa secara otomatis di *database*, atau mengirim *email reminder* H-3 sebelum langganan habis.
3. **Multi-Tier Plans:** Struktur `config/plans.php` sudah disiapkan, namun logika di UI (`pricing.blade.php`) masih didesain spesifik untuk satu jenis paket premium saja (Pro). Jika di masa depan ada paket "Enterprise" atau "Basic", UI tersebut harus dibuat dilooping (*dynamic iteration*).
4. **Tool Engine Execution:** Komponen penjalan *tool* aslinya (misal logika mengompres PDF atau gambar) mungkin perlu diintegrasikan lebih dalam tergantung *script processing* / *API 3rd party* yang akan digunakan.
5. **Admin Panel:** Saat ini belum ada antarmuka dasbor Admin untuk mengontrol total pengguna, melihat arus pendapatan (revenue), atau memblokir pengguna *(ban user)*.
6. **Invoice Enumeration:** Format nomor invoice saat ini mengandalkan `midtrans_order_id` yang mengandung kombinasi waktu dan string acak. Jika standar akuntansi perusahaan membutuhkan format penomoran sekuensial (contoh: `INV/2026/08/0001`), perlu dibuat kolom tambahan khusus di tabel `subscriptions`.

---

**Status Akhir Modul Billing:** 100% Selesai & Berfungsi dengan Baik. Siap untuk dilempar ke tahap uji coba produksi (*Production QA*).
