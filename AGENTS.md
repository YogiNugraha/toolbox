# 🧰 AGENTS.md — ToolBox Project Standards & AI Guidelines

Selamat datang di proyek **ToolBox** (Platform Alat Web Serbaguna: PDF, Gambar, Dokumen, dan Konversi).
Setiap agen AI yang bekerja di repositori ini **WAJIB** membaca dan mematuhi aturan berikut demi menjaga konsistensi arsitektur, kualitas kode, dan stabilitas fungsional.

---

## 1. Arsitektur & Tech Stack Inti

| Komponen | Teknologi & Versi | Catatan Khusus |
|---|---|---|
| **Backend Framework** | Laravel 13.8+ (PHP 8.3/8.4) | SQLite default (`database/database.sqlite`), Laragon virtual host |
| **Frontend Reactive** | Livewire 4.3 (Strictly Class-Based) | **DILARANG** menggunakan Single-File Component (SFC) atau Volt. Wajib ikuti konvensi class-based di `config/livewire.php` (`'type' => 'class'`) |
| **CSS & Design System** | Tailwind CSS v4 + Lineone UI Kit | Palet Slate / Navy (`dark:bg-navy-800`), font Inter/Outfit, token Lineone murni (`C:\laragon\www\lineone-laravel`) |
| **Tipografi & Font** | Lineone Standard Tokens | **Headings**: `font-semibold` / `font-medium`. **ZERO TOLERANCE** untuk `font-black` dan `font-extrabold`. DILARANG menggunakan arbitrary font sizes (`text-[9px]`, `text-[10px]`, `text-[11px]`). Wajib token standar Tailwind (`text-xs`, `text-sm`, `text-base`). |
| **Tombol (Buttons)** | Lineone Pill/Rounded (`h-9`/`h-10`) | `btn h-9 rounded-full bg-primary font-medium text-white shadow-xs text-xs px-5`. DILARANG efek colored glow shadow (`shadow-primary/*`). |
| **Ikon UI** | Blade Lucide Icons (`<x-lucide-...>`) | Konsisten menggunakan ikon dari library `mallardduck/blade-lucide-icons` |
| **Badge & Tag UI** | Murni Lineone UI Kit | Format: `badge rounded-full px-2.5 py-0.5 text-xs font-medium`. **DILARANG KERAS** menyisipkan emoji/icon (`🌟`, `👑`, `🔥`, `✨`, `<x-lucide-...>`) atau gradient buatan pada badge. Wajib teks bersih dengan palet Lineone. |
| **Anti-AI-Slop** | Clean UI Strict Rule | DILARANG menyisipkan bola gradasi ambient blur melayang (`filter blur-3xl opacity-20 bg-linear-to-r`), shadow raksasa (`shadow-2xl`), atau border rainbow. |
| **Notifikasi & Modal** | `App\Traits\LivewireLineoneAlerts` | Gunakan `$this->toast('Pesan', 'success|error|warning|info')` |
| **Mesin Pemrosesan File** | Python 3.10/3.13 (`PyMuPDF / fitz`, `pdf2docx`, `openpyxl`, `python-pptx`) + LibreOffice Headless | Skrip berada di `storage/scripts/*.py` |
| **Image Processing** | Intervention Image v4 (GD Driver) | EXIF otomatis di-strip saat encoding |
| **Asynchronous Jobs** | Laravel Queue (`database` connection) | Pekerjaan berat seperti PDF to Word dijalankan via `ConvertPdfToWordJob` |
| **Monetisasi & Limit** | `EntitlementService` + Midtrans Snap | Kuota bulanan, pembatasan ukuran file (`max_file_size_mb`), dan fitur Pro (`locked_features`) |

---

## 2. Aturan Baku Pengembangan Tool Baru (Tool Service Pattern)

Setiap fitur/tool dalam aplikasi ini mengikuti alur **4-Tier Pattern**:

1. **Definisi Konfigurasi (`config/tools.php`)**:
   - Daftarkan metadata tool: `slug`, `name`, `description`, `icon`, `category`, `component`, dan `lockable_features`.
   - Tool akan disinkronkan ke tabel `tools` oleh `Tool::syncFromConfig()`.

2. **Backend Processing Service (`app/Services/Tools/{Name}Service.php`)**:
   - Bertanggung jawab murni atas logika konversi/pemrosesan berkas.
   - Jika memanggil skrip Python di `storage/scripts/`:
     - Ambil path executable via `config('services.python_path', 'python')`.
     - Gunakan `Symfony\Component\Process\Process` dengan timeout terukur (60s–300s).
     - Kembalikan output JSON terstruktur yang memuat status, path hasil, ukuran awal, dan ukuran baru.
   - Simpan berkas hasil di disk `local` (`storage/app/private/users/{userId}/...`).

3. **Livewire Component (`app/Livewire/Tools/{Name}.php`)**:
   - Gunakan trait `WithFileUploads` dan `LivewireLineoneAlerts`.
   - Validasi file input (`mimes`, `max:51200`).
   - Pengecekan kuota melalui `EntitlementService`:
     ```php
     $remaining = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
     if ($remaining !== null && $remaining <= 0) { ... }
     ```
   - Catat riwayat ke model `Activity` dengan siklus:
     `status: 'processing'` → Eksekusi Service → `status: 'completed'` / `status: 'failed'`.
   - Tambahkan counter pemakaian: `\App\Models\Tool::incrementUsage($toolSlug)`.

4. **Blade View (`resources/views/livewire/tools/{kebab-case}.blade.php`)**:
   - Wajib memiliki 3 state utama:
     1. **Upload View**: Dropzone interaktif dengan progress bar upload Alpine.js (`x-on:livewire-upload-progress`).
     2. **Config/Preview View**: Kontrol parameter alat (misal: rotasi, kualitas, halaman).
     3. **Result/Download View**: Tombol unduh berkas hasil, statistik penghematan ukuran, dan opsi proses ulang.
   - Wajib mendukung tampilan Dark Mode (`dark:bg-navy-900`, `dark:text-navy-100`).

---

## 3. Aturan Khusus Lingkungan Windows / Laragon & Eksekusi Python

1. **Path Resolving di Skrip Python**:
   - Setiap skrip Python di `storage/scripts/*.py` **wajib** menyertakan fallback `site.addsitedir()` untuk path `site-packages` Windows/Laragon agar modul seperti `fitz` atau `pdf2docx` dapat ditemukan saat dijalankan oleh web server.
2. **Virtual Host Laragon**:
   - Jangan gunakan `php artisan serve` untuk pengetesan upload file besar. Aplikasi dikonfigurasi untuk berjalan di `http://toolbox.test` melalui Laragon.
3. **Penyimpanan Berkas Sementara**:
   - Folder penampung sementara adalah disk `local` (`storage/app/private`).
   - Berkas otomatis dibersihkan oleh scheduler `php artisan cleanup:temp` setelah berumur 24 jam (sesuai `FILE_RETENTION_HOURS`).

---

## 4. Keamanan & Hak Akses (Entitlements)

- **Admin Bypass**: Pengguna dengan status `is_admin = true` secara otomatis memiliki akses **unlimited** dan **bypass seluruh limit serta lock feature**.
- **Single Session**: Middleware `EnsureSingleSession` menjaga agar satu akun tidak login secara bersamaan di perangkat berbeda via `current_session_token`.
- **Download Protection**: Route `/download/{activity}` selalu memvalidasi `activity->user_id === auth()->id()`.

---

## 5. Sumber Daya Referensi Lanjutan

- Dokumentasi Aturan Detail: `.agents/rules/`
- Prosedur Runbook & Skills: `.agents/skills/`
- Peta Jaringan Arsitektur: `.agents/knowledge/`
