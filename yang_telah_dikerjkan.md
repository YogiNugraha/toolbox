# Laporan Pengerjaan Proyek: ToolBox (Web Tools)

Dokumen ini berisi rangkuman seluruh pekerjaan yang telah diselesaikan pada proyek ToolBox dari awal hingga saat ini. Dokumen ini disiapkan untuk memberikan konteks lengkap kepada AI atau developer lain yang akan melanjutkan proyek ini.

## 1. Arsitektur & Tech Stack Dasar
- **Framework:** Laravel 12.
- **Frontend:** Livewire v4 (namun dikonfigurasi dan ditulis dengan gaya class-based v3 sesuai instruksi `PROJECT_SPEC.md`), Alpine.js, dan Tailwind CSS (via Vite).
- **Database:** SQLite.
- **Queue:** Database driver (`QUEUE_CONNECTION=database`) untuk menjalankan task berat di background.

## 2. Fitur Autentikasi & Dashboard (Private System)
Sesuai dengan `PROJECT_SPEC_ADDENDUM_AUTH_DASHBOARD.md`, sistem publik telah diubah menjadi sistem privat.
- **Autentikasi (Login & Register):** Menggunakan fitur auth bawaan Laravel dipadukan dengan komponen Livewire (`App\Livewire\Auth\Login` dan `App\Livewire\Auth\Register`). Registrasi terbuka untuk umum dan langsung mengarahkan ke dashboard.
- **Dashboard & Routing:** 
  - Route root `/` akan me-redirect guest ke `/login` dan user yang sudah login ke `/dashboard`.
  - Halaman Dashboard menampilkan overview singkat, statistik, dan list aktivitas terakhir pengguna.
  - Halaman History (`/history`) menampilkan semua riwayat penggunaan tools dari user yang sedang login.
  - Halaman Profile (`/profile`) disiapkan untuk manajemen akun pengguna.
- **Activity Logging (Tabel `activities`):** Setiap kali user menggunakan tools, sistem merekam file asli, path hasil, ukuran asli, ukuran hasil, meta config yang digunakan, dan status pengerjaan (`processing`, `completed`, `failed`, `expired`). Activity log diikat secara relasional (foreign key) ke `user_id`.

## 3. Tools Utama yang Telah Diimplementasikan
Terdapat 3 alat bantu pemrosesan file utama yang dibangun menggunakan arsitektur `Livewire Component` + `Service Class` agar tetap bersih dan extensible:

### A. Compress Gambar (`ImageCompressor`)
- **Fungsi:** Mengompres gambar dengan dukungan format JPG, PNG, dan WebP.
- **Preset:**
  - `Sosial Media`: Max 1080px, quality 80.
  - `Website`: Max 1920px, quality 75, dengan opsi convert otomatis ke WebP.
  - `Custom`: Quality bisa diatur (1-100), opsi resize resolusi bebas, dan bisa pilih format output.
- **Service:** Menggunakan `Intervention\Image` via `ImageProcessorService`.

### B. Convert Format Gambar (`ImageConverter`)
- **Fungsi:** Mengonversi antar format gambar (JPG, PNG, WebP, GIF, BMP).
- **Alur:** User memilih gambar dan memilih target format di dropdown.
- **Service:** Menggunakan `ImageProcessorService` dengan quality default 90.

### C. PDF ke Word (`PdfToWord`)
- **Fungsi:** Mengonversi dokumen PDF ke format Word (.docx).
- **Alur:** Menggunakan sistem **Queue**. File diupload, status activity menjadi `processing`, kemudian job `ConvertPdfToWordJob` di-dispatch ke queue database.
- **Proses:** Memanggil script Python `pdf2docx` (sebelumnya dirancang menggunakan LibreOffice, namun untuk konversi yang lebih baik ke DOCX digunakan pendekatan Python `pdf2word.py` via `Symfony\Component\Process\Process`). UI Livewire akan melakukan `wire:poll` untuk memantau status secara realtime tanpa perlu refresh halaman.

## 4. Keamanan & Reliability (Hardening)
Sesuai dengan instruksi `HARDENING_BRIEF.md`, lapisan keamanan telah ditambahkan:
- **Rate Limiting:** Mencegah brute force dan spam.
  - Endpoint Guest (login/register) dilimit 5 request per menit (`throttle:5,1`).
  - Endpoint Auth (dashboard/tools) dilimit 20 request per menit (`throttle:20,1`).
- **Validasi MIME Asli (Anti Spoofing):** Pada komponen `ImageCompressor`, `ImageConverter`, dan `PdfToWord` telah ditambahkan kode validasi server-side (`$this->file->getMimeType()`) manual yang melempar exception apabila file ekstensi di-rename dari tipe berbahaya menjadi ekstensi aman.
- **Otorisasi Download:** Route unduhan (`/download/{activity}`) memverifikasi bahwa `auth()->id() === $activity->user_id` sebelum file disajikan (Streamed Download).
- **Retensi & Cleanup:** Command scheduler `CleanupTempFiles` (`php artisan cleanup:temp`) telah dibuat dan didaftarkan pada `routes/console.php` untuk berjalan setiap jam (hourly). Scheduler ini bertugas menghapus file fisik temporary/result yang lebih tua dari limit jam `FILE_RETENTION_HOURS` di `.env` (default 24 jam) dan merubah status tabel activity menjadi `expired`. *(Catatan: Di Windows/Laragon, Anda harus menjalankan `php artisan schedule:work` di terminal secara manual agar otomatisasi ini berjalan).*
- **Error Handling:** Setiap pemrosesan yang gagal akan langsung mengubah status activity menjadi `failed`, menghindarkan data dari status `processing` selamanya, dan UI akan menampilkan pesan kegagalan ke user.

## 5. UI/UX
- **Desain:** Mengusung tema Digital Workbench yang modern, minimalis, mobile responsive, dan memiliki sidebar (collapsible).
- **Upload Area:** Mendukung *drag and drop* interaktif menggunakan Alpine.js.
- **State Feedback:** Semua interaksi dipasangi state indikator loading (`wire:loading` atau custom loading spinner) agar UX terasa halus dan responsif.

## 6. Status Kesiapan Saat Ini
Seluruh **Definition of Done** (DoD) pada `PROJECT_SPEC.md`, `PROJECT_SPEC_ADDENDUM_AUTH_DASHBOARD.md`, dan `HARDENING_BRIEF.md` **telah terpenuhi (100% Selesai)**. Proyek ini sudah stabil secara fungsionalitas utama dan siap dikembangkan lebih lanjut apabila ada fitur/tools tambahan yang ingin dimasukkan ke depannya.
