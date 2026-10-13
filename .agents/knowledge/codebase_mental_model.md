# 🧠 Mental Model Kode & Pedoman Keputusan: ToolBox

Dokumen ini mencatat keputusan arsitektural penting (*architectural decision records / ADRs*), alasan di balik pemilihan teknologi, dan perangkap umum (*gotchas*) pada proyek ToolBox.

---

## 1. Mengapa Keputusan Ini Diambil?

### A. Livewire 4 dengan Konfigurasi Class-Based
- **Alasan**: Livewire v4 memperkenalkan Single-File Component (SFC) dan Volt secara default, namun untuk aplikasi enterprise/tools dengan state file uploads yang kompleks, class-based component memberikan pemisahan yang jauh lebih rapi antara properties Livewire, lifecycle hooks, injection service container, dan view template Blade.
- **Konsekuensi**: Jangan pernah membuat komponen single-file atau memindahkan logika PHP ke dalam file `.blade.php`.

### B. PyMuPDF (fitz) sebagai Mesin Vektor Utama PDF
- **Alasan**: PyMuPDF jauh lebih cepat, hemat memori, dan menghasilkan rotasi/kompresi/rendering vektor lossless dibandingkan library PHP murni seperti FPDF, TCPDF, atau FPDI.
- **Konsekuensi**: Server wajib memiliki interpreter Python terpasang dengan library `pymupdf`.

### C. pdf2docx vs LibreOffice Draw untuk PDF ke Word
- **Alasan**: LibreOffice Draw tidak mendukung ekspor langsung format dokumen Microsoft Word (.docx) dengan aliran teks editable (*reflowable text flow*). Library Python `pdf2docx` menganalisis struktur paragraf, tabel, dan gambar sehingga menghasilkan dokumen Word yang dapat disunting dengan baik.
- **Konsekuensi**: Tugas ini dialihkan ke antrean latar belakang (`ConvertPdfToWordJob`) karena memakan waktu dan komputasi intensif.

### D. Isolasi Profil LibreOffice untuk Office -> PDF
- **Alasan**: Secara default, jika LibreOffice dipanggil bersamaan dari beberapa proses web server dengan direktori pengguna default, LibreOffice akan memunculkan error *document lock* atau crash.
- **Konsekuensi**: Skrip `word2pdf.py`, `xlsx2pdf.py`, dan `pptx2pdf.py` membuat folder profil unik di direktori temporer dan melewatkannya via parameter `-env:UserInstallation`.

### E. Pure Light Mode & Fast Snappy UI
- **Alasan**: Dark mode di SPA Livewire rentan terhadap inkonsistensi hydration/state flash, dan tampilan Light Mode Lineone memberikan estetika bersih, profesional, dan tajam tanpa distorsi kontras. Animasi disederhanakan menjadi 100ms–150ms tanpa delay agar interaksi terasa responsif layaknya aplikasi desktop.
- **Konsekuensi**: Fitur dark mode dinonaktifkan (`isDarkModeEnabled: false`), script membersihkan `.dark` & `_x_darkMode_on`. Jangan menambahkan toggle dark mode.

### F. Aksi Baris Tabel: Elipsis Dropdown (`...`) via Lineone Popper
- **Alasan**: Menjejerkan banyak tombol aksi di baris tabel membuat tabel melebar secara horizontal dan berantakan. Selain itu, container tabel memiliki `overflow-x: auto` yang memotong dropdown CSS biasa (`absolute`). Lineone `usePopper` memakai strategi `fixed` viewport-relative sehingga menu dropdown mengambang bebas di atas container tanpa terpotong.
- **Konsekuensi**: Baris tabel dengan > 1 aksi wajib dirangkum ke tombol elipsis vertikal (`...`) menggunakan `usePopper({ placement: 'bottom-end', offset: 4 })`.

### G. Watermark & Security PDF Engine (PyMuPDF Morph + Pillow Alpha)
- **Alasan**: Memberi stempel teks miring pada PDF membutuhkan transformasi matriks affine rotasi (`morph=(center, Matrix(angle))`) dan perhitungan lebar teks eksak (`get_text_length`) agar posisi stempel selalu presisi di tengah atau grid pola berulang (*tiled*). Untuk watermark logo, Pillow menyesuaikan kanal transparansi alpha/RGBA sebelum di-stream ke `insert_image` PyMuPDF.
- **Konsekuensi**: Operasi berjalan lossless dan in-memory tanpa merusak tata letak maupun resolusi teks asli dokumen.

### H. In-Memory Image Suite (Intervention Image v4 Driver GD)
- **Alasan**: Operasi resize dan crop gambar tidak memerlukan dependensi eksternal Python atau FFmpeg; Intervention Image v4 dengan driver GD bekerja secara in-memory dalam hitungan milidetik. Menggunakan `scale(width, height)` untuk mempertahankan aspek rasio secara proporsional dan `crop(width, height, x, y)` untuk pemotongan presisi berbagai rasio preset (1:1, 4:3, 16:9, pas foto).
- **Konsekuensi**: Sangat cepat, hemat sumber daya server, dan tidak memerlukan eksekusi CLI latar belakang.

### I. Custom QR Code Suite (chillerlan/php-qrcode v6 + Intervention Image v4)
- **Alasan**: `chillerlan/php-qrcode` mendukung output ganda: raster PNG (`QRGdImagePNG`) dan vektor SVG murni (`QRMarkupSVG`) tanpa dependensi sistem C atau eksternal. Untuk fitur logo tengah, Intervention Image v4 menempelkan logo di atas canvas QR dengan badge putih melingkar dan otomatis beralih ke Error Correction Level H (30% redundansi) agar QR tetap mudah dipindai kamera smartphone.
- **Konsekuensi**: Pratinjau langsung instan (Live Sync data URI) dan ekspor vektor lossless berkualitas tinggi untuk cetak materi fisik maupun digital.

### J. Text & Word Intelligence Suite (Multibyte UTF-8 + Unicode Properties)
- **Alasan**: Menghitung kata menggunakan `str_word_count()` standar PHP gagal membaca karakter beraksen atau multibyte/bahasa non-Inggris. Digunakan regex properti Unicode `[\p{L}\p{N}]+/u` dan fungsi `mb_strlen(..., 'UTF-8')` agar perhitungan kata, karakter dengan/tanpa spasi, kalimat, dan paragraf 100% akurat di berbagai bahasa. Transformasi case (UPPERCASE, Title Case, Sentence case, camelCase, snake_case, kebab-case) dipadukan dengan stack undo internal pada Livewire untuk memberikan pengalaman penulisan yang aman dan bebas kesalahan.
- **Konsekuensi**: Eksekusi instan di memori, responsif di bawah 10ms, dan mendukung impor/ekspor file teks langsung.

### K. PDF Vector Pagination Engine (PyMuPDF Textbox + Dynamic Page Offsets)
- **Alasan**: Menambahkan nomor halaman pada dokumen resmi/skripsi memerlukan presisi tipografi tinggi tanpa merasterisasi teks asli. Digunakan `page.insert_textbox()` dengan 6 koordinat anchor (top/bottom, left/center/right) dan perataan native PyMuPDF. Mendukung pengabaian halaman sampul (`start_page`), penyesuaian angka awal (`first_number`), dan konversi angka Romawi (i, ii, iii / I, II, III).
- **Konsekuensi**: Berkas output berukuran sangat kecil (*deflate lossless*), teks nomor halaman dapat disalin/ditelusuri (*searchable*), dan didukung pratinjau thumbnail per halaman.

### L. Visual PDF Page Pruning & Garbage Clean Engine (PyMuPDF doc.select + Garbage Collect)
- **Alasan**: Menghapus halaman dari PDF sering meninggalkan objek yatim (*orphaned streams/xref tables*) yang membuat ukuran berkas tetap besar. Pendekatan `doc.select([kept_pages])` dikombinasikan dengan `doc.save(garbage=4, deflate=True)` membersihkan seluruh objek yang tidak terpakai, sehingga ukuran berkas hasil pemangkasan mengecil drastis. Thumbnail mini (skala 0.55x) di-render untuk interaktivitas grid visual 2 arah (klik kartu halaman tersinkron dengan teks rentang halaman).
- **Konsekuensi**: Sangat cepat, hemat kuota data pengguna, dan mencegah dokumen kosong (wajib menyisakan minimal 1 halaman).

---

## 2. Perangkap & Hal yang Sering Menjadi Masalah (Gotchas)

| Isu Potensial | Gejala | Solusi Baku |
|---|---|---|
| **Python Module Not Found** | Error `No module named 'fitz'` saat dieksekusi via Apache/Laragon | Skrip Python harus memanggil `site.addsitedir()` ke path site-packages Windows pengguna (sudah distandarkan di semua skrip `storage/scripts/*.py`). |
| **Batas Ukuran Upload PHP** | Livewire melempar error upload file pada file > 2MB | Pastikan `upload_max_filesize = 64M` dan `post_max_size = 64M` di `php.ini` Laragon. Aturan validasi Livewire disetel `max:51200` (50MB). |
| **URL Virtual Host Laragon** | Fitur download atau asset gagal dimuat di `localhost:8000` | Gunakan URL virtual host Laragon: `http://toolbox.test`. Jangan andalkan `php artisan serve`. |
| **Worker Queue Belum Aktif** | Status konversi PDF ke Word terus menerus *processing* tanpa selesai | Pastikan terminal worker queue sedang berjalan: `php artisan queue:work`. |
| **Quota Habis pada Akun Pengguna** | Muncul pesan error "Kuota bulanan Anda sudah habis" | Pengguna perlu dinaikkan paketnya ke Pro atau diberi status `is_admin = 1` di database untuk bypass kuota. |
| **Menu Dropdown Terpotong Tabel** | Dropdown aksi baris tabel terpotong atau tertutup garis scroll | Gunakan `x-data="usePopper({ placement: 'bottom-end', offset: 4 })"` dengan container `popper-root fixed` (strategi fixed viewport). |
| **Livewire Payload Too Large** | `Livewire\Exceptions\PayloadTooLargeException: Livewire request payload is too large` saat klik atau interaksi | DILARANG menyimpan string base64 / data biner di public property Livewire. Simpan thumbnail ke disk publik (`storage/app/public/temp_thumbs/...`) dan render via `asset(...)`. Naikkan juga `payload.max_size` di `config/livewire.php`. |

---

## 3. Checklist Sebelum Menerapkan Perubahan Baru
- [ ] Apakah komponen Livewire menggunakan class PHP terpisah di `app/Livewire/`?
- [ ] Apakah trait `LivewireLineoneAlerts` dan notifikasi `$this->toast()` digunakan untuk respons pengguna?
- [ ] Apakah baris tabel dengan > 1 aksi menggunakan elipsis titik tiga (`...`) dengan Lineone Popper fixed strategy?
- [ ] Apakah animasi dan transisi cepat (`duration-100` popper / `duration-150` modal)?
- [ ] Apakah UI mematuhi **Pure Light Mode** tanpa toggle dark mode dan tanpa AI-slop (tanpa icon dekoratif pada judul, tanpa badge mengambang)?
- [ ] Apakah pengecekan kuota `EntitlementService` dan pencatatan `Activity` telah diimplementasikan?
- [ ] Apakah path berkas hasil berada di disk `local` dan aman dari akses publik langsung?
- [ ] Apakah skrip Python menyertakan blok import `site.addsitedir()` untuk Windows/Laragon?
