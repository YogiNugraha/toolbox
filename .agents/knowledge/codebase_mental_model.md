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

---

## 2. Perangkap & Hal yang Sering Menjadi Masalah (Gotchas)

| Isu Potensial | Gejala | Solusi Baku |
|---|---|---|
| **Python Module Not Found** | Error `No module named 'fitz'` saat dieksekusi via Apache/Laragon | Skrip Python harus memanggil `site.addsitedir()` ke path site-packages Windows pengguna (sudah distandarkan di semua skrip `storage/scripts/*.py`). |
| **Batas Ukuran Upload PHP** | Livewire melempar error upload file pada file > 2MB | Pastikan `upload_max_filesize = 64M` dan `post_max_size = 64M` di `php.ini` Laragon. Aturan validasi Livewire disetel `max:51200` (50MB). |
| **URL Virtual Host Laragon** | Fitur download atau asset gagal dimuat di `localhost:8000` | Gunakan URL virtual host Laragon: `http://toolbox.test`. Jangan andalkan `php artisan serve`. |
| **Worker Queue Belum Aktif** | Status konversi PDF ke Word terus menerus *processing* tanpa selesai | Pastikan terminal worker queue sedang berjalan: `php artisan queue:work`. |
| **Quota Habis pada Akun Pengguna** | Muncul pesan error "Kuota bulanan Anda sudah habis" | Pengguna perlu dinaikkan paketnya ke Pro atau diberi status `is_admin = 1` di database untuk bypass kuota. |

---

## 3. Checklist Sebelum Menerapkan Perubahan Baru
- [ ] Apakah komponen Livewire menggunakan class PHP terpisah di `app/Livewire/`?
- [ ] Apakah trait `LivewireLineoneAlerts` dan notifikasi `$this->toast()` digunakan untuk respons pengguna?
- [ ] Apakah pengecekan kuota `EntitlementService` dan pencatatan `Activity` telah diimplementasikan?
- [ ] Apakah path berkas hasil berada di disk `local` dan aman dari akses publik langsung?
- [ ] Apakah skrip Python menyertakan blok import `site.addsitedir()` untuk Windows/Laragon?
- [ ] Apakah desain tampilan mendukung Dark Mode tema Lineone (`dark:bg-navy-900`)?
