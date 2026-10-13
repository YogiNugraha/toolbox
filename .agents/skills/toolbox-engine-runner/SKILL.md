---
name: toolbox-engine-runner
description: >-
  Use this skill when diagnosing, running, testing, or debugging Python processing scripts,
  LibreOffice headless conversions, Intervention Image processing, or background queue jobs
  in the ToolBox project on Windows/Laragon.
---

# ⚙️ Toolbox Engine Runner & Troubleshooting Runbook

Skill ini digunakan untuk menguji, mendiagnosis, dan memvalidasi mesin pemrosesan dokumen dan berkas pada ToolBox (Python, LibreOffice, Queue Worker, Image Intervention).

## 1. Verifikasi Lingkungan Python & Modul

Aplikasi memanggil interpreter Python yang didefinisikan pada `.env` (`PYTHON_PATH`).

### Cek Versi Python & Modul Terpasang
Jalankan perintah berikut di terminal:
```powershell
python --version
python -c "import fitz; print('PyMuPDF Version:', fitz.__version__)"
python -c "import pdf2docx; print('pdf2docx OK')"
python -c "import openpyxl; print('openpyxl OK')"
python -c "import pptx; print('python-pptx OK')"
```

Jika modul tidak ditemukan pada sesi web server:
- Pastikan skrip di `storage/scripts/*.py` memuat blok pencarian direktori `site.addsitedir()` ke `~\AppData\Roaming\Python\Python313\site-packages` atau direktori Python yang aktif.

---

## 2. Pengujian Skrip Python Mandiri (CLI Standalone Test)

Setiap skrip di `storage/scripts/` dapat diuji langsung dari terminal untuk memastikan logika pemrosesan berjalan normal sebelum diintegrasikan ke Livewire:

### Contoh Test Rotate PDF:
```powershell
python storage/scripts/rotate_pdf.py test_edit.pdf --count
python storage/scripts/rotate_pdf.py test_edit.pdf storage/app/private/test_rotated.pdf "{\"1\": 90}" 0
```

### Contoh Test Compress PDF:
```powershell
python storage/scripts/compress_pdf.py test_edit.pdf storage/app/private/test_compressed.pdf medium
```

### Contoh Test Word ke PDF (LibreOffice):
```powershell
python storage/scripts/word2pdf.py storage/scripts/sample.docx storage/app/private/sample.pdf
```

### Contoh Test Hapus Halaman PDF:
```powershell
python storage/scripts/remove_pdf_pages.py --inspect test.pdf
python storage/scripts/remove_pdf_pages.py --thumbnails test.pdf storage/app/public/temp_thumbs/test 12
python storage/scripts/remove_pdf_pages.py test.pdf storage/app/private/test_pruned.pdf "2, 4-5"
```

### Contoh Test Penomoran Halaman PDF:
```powershell
python storage/scripts/page_number_pdf.py test.pdf storage/app/private/test_numbered.pdf bottom_center 1 1 standard normal
```

---

## 3. Menjalankan Queue Worker untuk Asynchronous Jobs

Fitur seperti konversi PDF ke Word (`ConvertPdfToWordJob`) diproses di latar belakang menggunakan database queue.

### Menjalankan Queue Worker Lokal:
```powershell
php artisan queue:work --tries=1 --timeout=300
```
Atau untuk continuous listening saat development:
```powershell
php artisan queue:listen --tries=1 --timeout=0
```

### Memeriksa Antrean Gagal:
```powershell
php artisan queue:failed
php artisan queue:retry all
```

---

## 4. Pembersihan Berkas Sementara (Storage Cleanup)

Berkas hasil pemrosesan disimpan pada disk `local` di `storage/app/private/users/{userId}/`.

Jalankan perintah pembersihan manual untuk berkas yang telah melewati batas retensi:
```powershell
php artisan cleanup:temp
```

Perintah ini akan:
1. Menghapus fisik berkas dari disk `local`.
2. Mengubah status baris `activities` terkait menjadi `expired`.
3. Mengosongkan kolom `result_path` untuk mencegah unduhan berkas kedaluwarsa.
