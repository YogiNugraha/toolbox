# 🧰 PROJECT SPEC — ToolBox (Web Tools: Compress, Convert, dll)

> Dokumen ini adalah panduan lengkap untuk AI coding agent membangun project ini dari nol sampai selesai (final). Ikuti urutan dan aturan di bawah ini dengan disiplin, terutama bagian **CATATAN PENTING soal Livewire** karena ada quirk versi yang WAJIB ditangani dengan benar.

---

## 1. Ringkasan Proyek

Website berisi kumpulan tools online (mirip iLovePDF / TinyPNG / Convertio), dijalankan sendiri (self-hosted). Setiap tools adalah halaman terpisah dengan alur: **upload file → proses di server → preview hasil → download**.

**Fitur prioritas utama (WAJIB ada dan berfungsi sempurna):**
1. **Compress Gambar** — dengan pilihan preset kualitas (lihat detail di bagian 6.2)
2. PDF to Word
3. JPG to PNG (image format converter)

Arsitektur harus **extensible**: menambah tools baru di masa depan harus semudah mungkin (tambah 1 service class + 1 Livewire component + 1 entry config), tanpa perlu ubah struktur inti.

---

## 2. Environment & Tech Stack

Environment developer (sudah tersedia, jangan install ulang):
- OS: Windows + **Laragon**
- PHP **8.4**
- Composer sudah terinstall
- Node.js + npm sudah terinstall
- Editor/agent: **Antigravity**

Stack yang WAJIB dipakai:
| Komponen | Versi/Pilihan |
|---|---|
| Framework | **Laravel 12** |
| Frontend interaktif | **Livewire** (lihat catatan versi di bagian 3 — WAJIB dibaca) |
| CSS | Tailwind CSS (versi terbaru yang kompatibel dengan Vite plugin Laravel 12) |
| JS ringan | Alpine.js (otomatis ikut terpasang bareng Livewire) |
| Database | SQLite (default, paling minim setup). Kalau ada alasan kuat butuh MySQL, boleh pakai MySQL via Laragon, tapi default-nya SQLite |
| Image processing | `intervention/image` (v3, kompatibel Laravel 12) |
| Image optimizer tambahan (opsional/advanced) | `spatie/image-optimizer` — **catatan:** butuh binary eksternal (jpegoptim, pngquant, cwebp, dll) yang harus diinstall terpisah di Windows. Jangan jadikan ini dependency wajib di awal, jadikan enhancement opsional setelah fitur utama (Intervention Image) jalan.
| PDF → Word | LibreOffice headless (`soffice --headless --convert-to docx`) dipanggil via `Symfony\Component\Process\Process` |
| Queue | Database driver (`QUEUE_CONNECTION=database`) untuk proses berat (PDF to Word). Untuk proses ringan (compress gambar) tidak wajib queue, cukup loading state di Livewire. |

---

## 3. ⚠️ CATATAN PENTING — Livewire Versi (WAJIB DIBACA & DIIKUTI)

Saat ini `composer require livewire/livewire` akan menginstall **Livewire v4** (bukan v3), karena v3 sudah tidak jadi versi default dan sering bentrok versi `symfony/*` dengan dependency Laravel 12 terbaru. Memaksa `composer require livewire/livewire:^3.0` **tidak disarankan** — akan sering menyebabkan conflict.

**Solusi: install Livewire v4, tapi WAJIB dikonfigurasi & dikoding dengan gaya/konvensi v3 (class-based component, dua file terpisah: PHP class + Blade view).** Alasan: Livewire v4 secara default menggunakan **single-file component** (satu file `⚡nama.blade.php` berisi PHP+Blade sekaligus, biasanya di `resources/views/components/`), format baru ini yang membuat `php artisan livewire:make counter` terasa "tidak membuat class" — itu bukan bug, itu default baru. Livewire v4 tetap **backward compatible** dan resmi mendukung mode class-based lama.

### Langkah wajib setelah install:

1. Install seperti biasa:
   ```bash
   composer require livewire/livewire
   ```
2. Publish config Livewire:
   ```bash
   php artisan livewire:publish --config
   ```
3. Buka `config/livewire.php`, cari key `make_command`, set supaya default generate class-based (gaya v3), bukan single-file:
   ```php
   'make_command' => [
       'type' => 'class',   // paksa gaya v3: PHP class + Blade view terpisah
       'emoji' => false,    // matikan prefix emoji ⚡ di nama file
   ],
   ```
4. Meskipun sudah diset default ke `class`, **selalu tetap gunakan flag `--class` secara eksplisit** setiap generate component untuk jaga-jaga (sesuai temuan user, kadang perlu eksplisit):
   ```bash
   php artisan make:livewire Tools/ImageCompressor --class
   ```
   > Catatan: command generator di v4 bernama `make:livewire` (bukan `livewire:make` seperti di v3 lama). Kalau agent menjalankan `livewire:make` dan error "command not found", ganti ke `make:livewire`.
5. Semua kode yang ditulis agent HARUS mengikuti gaya v3:
   - Class di `app/Livewire/...` extends `Livewire\Component`
   - View terpisah di `resources/views/livewire/...`
   - Render pakai method `render()` return `view(...)`
   - Pemanggilan di Blade pakai tag `<livewire:nama-komponen />` atau `@livewire('nama-komponen')`
   - **JANGAN** gunakan fitur baru v4 seperti single-file component (⚡file), Islands, atau `Route::livewire()` — cukup pakai routing biasa (`Route::get(...)->name(...)` yang return view berisi tag livewire), supaya kode tetap terasa dan berperilaku seperti v3 murni.

Jika ternyata di tengah proses instalasi ada error dependency yang tidak bisa diatasi meski sudah ikut langkah di atas, agent boleh mencoba `composer require livewire/livewire:^3.0 --with-all-dependencies` sebagai alternatif terakhir — tapi prioritaskan solusi v4-dengan-gaya-v3 di atas dulu karena lebih stabil untuk Laravel 12.

---

## 4. Setup Awal (langkah demi langkah)

```bash
# 1. Buat project baru
composer create-project laravel/laravel toolbox
cd toolbox

# 2. Install Livewire (ikuti bagian 3 di atas setelah ini)
composer require livewire/livewire
php artisan livewire:publish --config
# -> edit config/livewire.php sesuai instruksi bagian 3

# 3. Setup database (SQLite, paling simpel)
# pastikan file database/database.sqlite ada, dan .env:
# DB_CONNECTION=sqlite
touch database/database.sqlite   # atau buat manual via file explorer di Windows
php artisan migrate

# 4. Install Tailwind CSS (via Vite plugin resmi — ikuti dokumentasi Tailwind terbaru
# untuk cara instalasi plugin Vite karena command bisa berubah versi ke versi)
npm install
npm install -D tailwindcss @tailwindcss/vite

# 5. Install package image processing
composer require intervention/image

# 6. Install package pendukung PDF
composer require symfony/process

# 7. Setup queue (untuk job berat seperti PDF to Word)
# .env: QUEUE_CONNECTION=database
php artisan queue:table
php artisan migrate

# 8. LibreOffice (untuk fitur PDF to Word) HARUS diinstall manual di Windows
# Download & install dari https://www.libreoffice.org/download/download/
# Setelah install, catat path soffice.exe, biasanya:
# C:\Program Files\LibreOffice\program\soffice.exe
# Simpan path ini di .env sebagai variable baru:
# LIBREOFFICE_PATH="C:\Program Files\LibreOffice\program\soffice.exe"

# 9. Jalankan
php artisan serve
npm run dev
php artisan queue:work   # jalankan di terminal terpisah, wajib untuk job PDF to Word
```

---

## 5. Struktur Folder

```
app/
  Livewire/
    Tools/
      ImageCompressor.php
      ImageConverter.php      (JPG<->PNG dkk)
      PdfToWord.php
  Services/
    Tools/
      ImageProcessorService.php   # logic compress & convert gambar (pakai Intervention Image)
      PdfConverterService.php     # logic panggil LibreOffice headless
  Jobs/
    ConvertPdfToWordJob.php
  Console/
    Commands/
      CleanupTempFiles.php        # scheduled command hapus file temp lama

resources/
  views/
    layouts/
      app.blade.php
      tool.blade.php              # layout khusus halaman tools (header, breadcrumb, dsb)
    livewire/
      tools/
        image-compressor.blade.php
        image-converter.blade.php
        pdf-to-word.blade.php
    home.blade.php                # halaman utama, grid daftar tools

config/
  tools.php                       # registry semua tools (slug, nama, deskripsi, icon, komponen)

routes/
  web.php
```

**Prinsip penting:** logic pemrosesan file (resize, compress, convert) TIDAK ditulis langsung di dalam Livewire component. Livewire component hanya mengurus UI/state (upload, pilihan preset, trigger aksi). Logic berat diletakkan di `app/Services/Tools/*Service.php` supaya reusable dan gampang di-test.

### `config/tools.php` (contoh struktur, sebagai registry tools)
```php
return [
    [
        'slug' => 'compress-image',
        'name' => 'Compress Gambar',
        'description' => 'Kompres ukuran gambar tanpa mengurangi kualitas visual secara signifikan.',
        'icon' => 'photo',
        'category' => 'Image',
        'component' => 'tools.image-compressor',
    ],
    [
        'slug' => 'convert-image',
        'name' => 'Convert Format Gambar',
        'description' => 'Ubah JPG ke PNG, PNG ke WebP, dan sebaliknya.',
        'icon' => 'arrows-right-left',
        'category' => 'Image',
        'component' => 'tools.image-converter',
    ],
    [
        'slug' => 'pdf-to-word',
        'name' => 'PDF ke Word',
        'description' => 'Konversi file PDF menjadi dokumen Word (.docx) yang bisa diedit.',
        'icon' => 'document-text',
        'category' => 'Document',
        'component' => 'tools.pdf-to-word',
    ],
];
```
Halaman utama (`home.blade.php`) me-render grid card dari `config('tools')` — jadi menambah tools baru cukup tambah 1 array di file ini + buat component-nya, tanpa sentuh halaman utama.

---

## 6. Spesifikasi Fitur

### 6.1 Halaman Utama
- Grid/list card semua tools, dikelompokkan per `category` (Image, Document, dst).
- Tiap card: icon, nama, deskripsi singkat, klik → masuk ke halaman tool.
- Search box sederhana (client-side, filter card berdasarkan nama, pakai Alpine.js — tidak perlu roundtrip ke server).

### 6.2 🎯 PRIORITAS UTAMA — Compress Gambar

Ini fitur paling penting, harus dibuat dengan detail dan benar-benar berfungsi.

**UI/Alur:**
1. User upload gambar (drag & drop area + tombol pilih file). Terima format: jpg, jpeg, png, webp. Maks ukuran file misal 10MB.
2. User memilih **preset kualitas** (pilihan berupa button group/radio, bukan dropdown, biar jelas):

   | Preset | Tujuan | Setting teknis |
   |---|---|---|
   | 📱 **Sosial Media** | Upload cepat ke IG/WA/FB, kualitas visual tetap bagus di layar HP | Resize sisi terpanjang maks **1080px**, quality encode **80**, strip metadata EXIF, output format sama seperti input (kecuali PNG besar → boleh dikonversi ke JPG untuk hasil lebih kecil, beri toggle) |
   | 🌐 **Website** | Optimasi kecepatan loading web | Resize sisi terpanjang maks **1920px**, quality encode **75**, strip metadata EXIF, tawarkan opsi konversi ke **WebP** (toggle "convert to WebP" karena WebP jauh lebih kecil) |
   | ⚙️ **Custom** | Kontrol manual | Slider quality manual **1–100**, checkbox opsional "Resize gambar" dengan input width/height manual, dropdown pilih format output (Original / JPG / PNG / WebP) |

3. Setelah preset dipilih, tampilkan tombol "Compress" (atau proses otomatis on-change untuk UX lebih cepat — pilih salah satu, boleh otomatis).
4. Selama proses: tampilkan loading state (`wire:loading`, disable input).
5. Setelah selesai, tampilkan:
   - Preview gambar hasil compress
   - Perbandingan: **ukuran asli vs ukuran hasil** + persentase penghematan (misal "2.4 MB → 340 KB, hemat 86%")
   - Tombol **Download**
6. Bonus (kalau memungkinkan tanpa merumitkan): dukung **multi-upload/batch**, dengan tombol "Download semua (ZIP)".

**Backend logic (`ImageProcessorService`):**
- Pakai `Intervention\Image\ImageManager`.
- Terima parameter: file, quality (int), max_dimension (nullable), output_format (nullable).
- Simpan file sementara di `storage/app/private/temp/{uuid}.{ext}` — jangan simpan di `public` langsung, serve lewat route terproteksi/streaming supaya file tidak bisa ditebak-tebak URL-nya.
- Return info: path hasil, ukuran asli (bytes), ukuran hasil (bytes).

**Validasi:**
- Validasi MIME type asli file (bukan cuma cek ekstensi), pakai rule Laravel `mimes:jpg,jpeg,png,webp` + cek `getMimeType()` manual sebagai lapisan tambahan.
- Cek `upload_max_filesize` & `post_max_size` di `php.ini` Laragon — kalau mau terima file sampai 10MB, pastikan kedua value itu di php.ini minimal 10M (default php.ini biasanya cuma 2M/8M), catat ini sebagai bagian dari setup.

### 6.3 PDF to Word

- Upload PDF (maks misal 20MB).
- Karena konversi butuh waktu (beberapa detik–menit tergantung ukuran), **WAJIB pakai queue**:
  1. Livewire component simpan file, dispatch `ConvertPdfToWordJob`.
  2. Job memanggil LibreOffice headless via `Process`:
     ```php
     $process = new Process([
         env('LIBREOFFICE_PATH'),
         '--headless',
         '--convert-to', 'docx',
         '--outdir', $outputDir,
         $inputPath,
     ]);
     $process->setTimeout(120);
     $process->run();
     ```
  3. Job update status (misal simpan status di cache/session per request-id, atau tabel kecil `conversions` kalau mau lebih robust).
  4. Livewire component polling status pakai `wire:poll.2s="checkStatus"` sampai status = selesai, baru tombol download muncul.
- Kalau agent kesulitan setup queue worker di awal (karena harus jalankan `php artisan queue:work` manual di terminal terpisah), boleh sementara set `QUEUE_CONNECTION=sync` untuk development supaya job jalan langsung tanpa worker terpisah — tapi catat ini sebagai catatan di README, dan untuk versi "final/production" tetap arahkan ke queue asli (`database` + `queue:work`, idealnya nanti pakai Supervisor kalau deploy).

### 6.4 JPG to PNG / Image Format Converter

- Upload gambar apapun (jpg/png/webp/gif/bmp).
- Dropdown pilih format output.
- Convert & download. Reuse `ImageProcessorService` yang sama dengan tool compress (parameter `output_format` saja, quality default 90 karena tujuannya convert bukan compress).

### 6.5 Roadmap Tools Tambahan (tidak wajib dikerjakan sekarang, tapi arsitektur harus siap)
Resize Image manual, Image to Base64, Compress PDF, Merge/Split PDF, Word to PDF. Setiap tools baru = tambah service + component + entry `config/tools.php`, tanpa ubah struktur inti.

---

## 7. UI/UX Guidelines

- Desain bersih, modern, minimalis. Mobile-first & responsive penuh.
- Layout konsisten: pakai 1 layout `layouts/tool.blade.php` untuk semua halaman tools (header + breadcrumb "Home > Compress Gambar" + judul + deskripsi singkat + area konten).
- Upload area bergaya drag-and-drop yang jelas (border dashed, berubah warna saat drag-over/hover).
- Semua tombol aksi harus punya `wire:loading` state (spinner/disable) — user harus selalu tahu sistem sedang memproses.
- Warna & branding bebas ditentukan agent asal konsisten, gunakan Tailwind utility classes saja (hindari custom CSS berlebihan).

---

## 8. Keamanan & Kebersihan Sistem

- Validasi MIME type asli di server-side untuk semua upload (jangan percaya ekstensi file saja).
- Rate limiting per IP di route tools, misal maksimal 20 request/menit (`throttle:20,1` middleware).
- **Auto-cleanup**: buat scheduled command (`app/Console/Commands/CleanupTempFiles.php`) yang jalan tiap 1 jam via Laravel Scheduler, menghapus semua file di `storage/app/private/temp/` yang lebih tua dari 1 jam. Daftarkan di `routes/console.php` (Laravel 12 style) pakai `Schedule::command('cleanup:temp')->hourly();`.
- File hasil proses TIDAK boleh diakses lewat URL publik langsung yang bisa ditebak — serve download lewat route terproteksi yang stream file (`response()->download()` atau `Storage::response()`), idealnya dengan token/uuid unik per sesi upload.

---

## 9. Definition of Done (checklist verifikasi sebelum dianggap selesai)

- [ ] `php artisan serve` jalan tanpa error
- [ ] Livewire terinstall & **semua component menggunakan format class-based (bukan single-file v4)**, sesuai bagian 3
- [ ] Halaman utama menampilkan grid tools dari `config/tools.php`, bukan hardcode di Blade
- [ ] **Compress Gambar**: 3 preset (Sosial Media / Website / Custom) semuanya berfungsi, hasil compress valid & bisa dibuka, menampilkan perbandingan ukuran asli vs hasil, dan file bisa didownload
- [ ] **PDF to Word**: konversi berhasil menggunakan LibreOffice headless via queue job, hasil `.docx` bisa dibuka di Word/LibreOffice Writer tanpa corrupt
- [ ] **JPG ↔ PNG converter** berfungsi untuk kombinasi format yang didukung
- [ ] Validasi file (tipe & ukuran) berjalan dan menolak file yang tidak sesuai dengan pesan error yang jelas
- [ ] File temporary otomatis terhapus (cleanup scheduler aktif)
- [ ] Tampilan responsive di ukuran layar mobile
- [ ] Tidak ada file `.env` atau kredensial yang ter-commit / ter-expose

---

## 10. Command Cheat Sheet

```bash
# Generate Livewire component (WAJIB pakai --class, lihat bagian 3)
php artisan make:livewire Tools/NamaTool --class

# Generate service class manual (bukan artisan, tulis manual di app/Services/Tools/)

# Generate job
php artisan make:job ConvertPdfToWordJob

# Generate scheduled command
php artisan make:command CleanupTempFiles

# Jalankan semuanya pas development (3 terminal terpisah)
php artisan serve
npm run dev
php artisan queue:work
```

---

## 11. Catatan Akhir untuk Agent

- Prioritaskan fitur **Compress Gambar** sampai benar-benar matang (preset kualitas, before/after size, download) sebelum lanjut ke tools lain — ini fitur utama yang paling ditunggu.
- Kalau ada keputusan teknis yang ambigu dan tidak dijelaskan detail di dokumen ini, pilih pendekatan paling simpel & stabil dulu (jangan over-engineer), catat asumsi yang diambil di README project.
- Tulis `README.md` di root project berisi ringkasan cara menjalankan project ini dari nol (hasil dari bagian 4 di atas), supaya siapapun (termasuk user) bisa jalankan ulang project ini di komputer lain.
