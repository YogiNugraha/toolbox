# 🧰 ToolBox (Web Tools: Compress, Convert, dll)

Website berisi kumpulan tools online (mirip iLovePDF / TinyPNG / Convertio), dijalankan sendiri (self-hosted). Setiap tools adalah halaman terpisah dengan alur: upload file → proses di server → preview hasil → download.

## Kebutuhan Sistem
- OS: Windows / Linux
- PHP 8.4+
- Composer
- Node.js & NPM
- SQLite (default) / MySQL
- **LibreOffice** (Wajib diinstall manual untuk fitur PDF ke Word)

## Cara Menjalankan Project

1. **Clone Repository (atau copy folder ini)**
2. **Install Dependensi PHP & NPM**
   ```bash
   composer install
   npm install
   ```
3. **Setup Environment**
   - Copy `.env.example` menjadi `.env` (Jika belum ada)
   - Jalankan `php artisan key:generate`
   - Pastikan variabel ini diset di `.env`:
     ```env
     DB_CONNECTION=sqlite
     QUEUE_CONNECTION=database
     LIBREOFFICE_PATH="C:\Program Files\LibreOffice\program\soffice.exe"
     ```
     *(Sesuaikan `LIBREOFFICE_PATH` dengan letak instalasi LibreOffice Anda)*
4. **Setup Database (SQLite)**
   Buat file database kosong jika belum ada dan jalankan migrasi:
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```
5. **Jalankan Aplikasi**
   Untuk development lokal, buka 3 terminal terpisah dan jalankan masing-masing:
   **Terminal 1:** (Compile Frontend Asset via Vite)
   ```bash
   npm run dev
   ```
   
   **Terminal 2:** (Worker untuk proses background PDF to Word)
   ```bash
   php artisan queue:work
   ```

6. Karena kamu menggunakan Laragon, buka browser di alamat: `http://toolbox.test` (jangan gunakan `php artisan serve` karena server bawaan PHP kurang stabil untuk upload file berukuran besar).

## Asumsi dan Keputusan Teknis
- **Livewire**: Menggunakan Livewire v4 namun dipaksa menggunakan konvensi Class-Based component ala v3 melalui konfigurasi `config/livewire.php` (`'type' => 'class'`).
- **Intervention Image**: Menggunakan v3 dengan GD Driver. Metadata EXIF akan di-strip secara default oleh library ini ketika encoding format dilakukan.
- **Queue**: Menggunakan driver `database`. Sangat penting menjalankan `php artisan queue:work` agar konversi PDF ke Word bisa diproses.
- **Cleanup**: Folder `storage/app/private/temp` menampung file sementara. Sebuah command scheduler `cleanup:temp` diatur jalan tiap jam untuk menghapus file yang berumur lebih dari 1 jam. Gunakan cron/scheduler worker Laravel untuk mengeksekusi ini di production.
