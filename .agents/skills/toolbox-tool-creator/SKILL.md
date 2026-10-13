---
name: toolbox-tool-creator
description: >-
  Use this skill when creating a new tool or modifying an existing tool in the ToolBox project,
  guiding through configuration in config/tools.php, database sync, backend processing service,
  Python execution script, Livewire component, and Lineone Blade view.
---

# 🛠️ Toolbox Tool Creator Runbook

Skill ini memandu proses pembuatan tool baru pada aplikasi ToolBox secara menyeluruh dan sesuai konvensi arsitektur proyek.

## Ringkasan Alur Pembuatan

Setiap tool di ToolBox dibangun melalui 5 langkah terpadu:
1. Pendaftaran konfigurasi di `config/tools.php` (kategori: `Document`, `Image`, atau `Utility`)
2. Sinkronisasi metadata ke basis data (`Tool::syncFromConfig()`)
3. Pembuatan skrip pemroses (Python di `storage/scripts/`, Intervention Image v4, `chillerlan/php-qrcode`, atau Text Processing Service murni)
4. Pembuatan Service wrapper di `app/Services/Tools/`
5. Pembuatan Livewire Component & Blade Template di `app/Livewire/Tools/` dan `resources/views/livewire/tools/`

---

## Langkah 1: Registrasi Konfigurasi Tool

Buka [config/tools.php](file:///c:/laragon/www/toolbox/config/tools.php) dan tambahkan entri tool:

```php
[
    'slug' => 'nama-tool',
    'name' => 'Nama Tampilan Tool',
    'description' => 'Penjelasan ringkas apa yang dilakukan alat ini.',
    'icon' => 'wrench', // Lucide icon identifier (tanpa prefix "lucide-")
    'category' => 'Document', // Atau 'Image'
    'component' => 'tools.nama-tool', // Format kebab-case component
    'lockable_features' => [
        'nama_fitur_pro' => 'Deskripsi Fitur Khusus Pro',
    ],
],
```

Sinkronkan ke tabel database via terminal:
```powershell
php artisan tinker --execute="App\Models\Tool::syncFromConfig();"
```

---

## Langkah 2: Skrip Pemroses Python (Jika Konversi Dokumen/PDF)

Jika operasi membutuhkan manipulasi PDF atau Office, buat file baru di `storage/scripts/{nama_tool}.py`.

**Template Baku Skrip Python:**
```python
#!/usr/bin/env python3
import sys
import os
import json
import site

# Wajib: Registrasi site-packages untuk runtime Windows/Laragon
_potential_site_dirs = [
    os.path.expanduser(r"~\AppData\Roaming\Python\Python313\site-packages"),
    r"C:\Users\Yogi Nugraha\AppData\Roaming\Python\Python313\site-packages",
    r"C:\Python313\Lib\site-packages",
    r"C:\laragon\bin\python\python-3.10\Lib\site-packages",
]
for _dir in _potential_site_dirs:
    if os.path.exists(_dir) and _dir not in sys.path:
        site.addsitedir(_dir)

def process_file(input_path, output_path, **kwargs):
    if not os.path.exists(input_path):
        raise FileNotFoundError(f"Input file tidak ditemukan: {input_path}")
    
    original_size = os.path.getsize(input_path)
    output_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(output_dir, exist_ok=True)
    
    # --- Logika manipulasi (misal PyMuPDF / fitz) ---
    # doc = fitz.open(input_path)
    # doc.save(output_path)
    # doc.close()
    
    new_size = os.path.getsize(output_path)
    return {
        "status": "success",
        "original_size": original_size,
        "new_size": new_size,
        "output_path": output_path
    }

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({"status": "error", "message": "Argumen tidak lengkap."}))
        sys.exit(1)
        
    input_path = os.path.abspath(sys.argv[1])
    output_path = os.path.abspath(sys.argv[2])
    
    try:
        res = process_file(input_path, output_path)
        print(json.dumps(res))
        sys.exit(0)
    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))
        sys.exit(1)
```

---

## Langkah 3: Pembuatan Tool Service

Buat file `app/Services/Tools/{Name}Service.php`.

```php
<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SampleToolService
{
    public function process(UploadedFile $file, array $options = []): array
    {
        $pythonScript = base_path('storage/scripts/sample_tool.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Skrip pemroses tidak ditemukan.");
        }

        // Siapkan direktori lokal
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'processed_' . $uniqueId . '.pdf';
        $relativeResultPath = $relativeDir . '/' . $outputFilename;

        Storage::disk('local')->makeDirectory($relativeDir);
        $absoluteResultPath = Storage::disk('local')->path($relativeResultPath);

        // Siapkan input file
        $realInputPath = $file->getRealPath();
        $cleanupInput = false;
        if (!$realInputPath || !file_exists($realInputPath)) {
            $tempRelative = $file->store('temp_tools', 'local');
            $realInputPath = Storage::disk('local')->path($tempRelative);
            $cleanupInput = true;
        }

        // Jalankan proses
        $process = new Process([
            $pythonExecutable,
            $pythonScript,
            $realInputPath,
            $absoluteResultPath,
        ]);
        $process->setTimeout(120);
        $process->run();

        if ($cleanupInput && isset($tempRelative)) {
            Storage::disk('local')->delete($tempRelative);
        }

        $output = trim($process->getOutput());
        if (preg_match('/\{.*\}/s', $output, $matches)) {
            $data = json_decode($matches[0], true);
            if (isset($data['status']) && $data['status'] === 'success') {
                $origBasename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                return [
                    'result_path' => $relativeResultPath,
                    'result_filename' => $origBasename . '_processed.pdf',
                    'original_size' => $data['original_size'] ?? $file->getSize(),
                    'new_size' => $data['new_size'] ?? (file_exists($absoluteResultPath) ? filesize($absoluteResultPath) : 0),
                ];
            }
            throw new \Exception($data['message'] ?? 'Gagal memproses berkas.');
        }

        throw new \Exception("Terjadi kesalahan: " . ($process->getErrorOutput() ?: $output));
    }
}
```

---

## Langkah 4: Pembuatan Livewire Component

Buat file `app/Livewire/Tools/{Name}.php`:
- Implementasikan trait `WithFileUploads` dan `LivewireLineoneAlerts`.
- Sertakan validasi file, pengecekan kuota `EntitlementService`, dan perekaman `Activity`.

---

## Langkah 5: Pembuatan Blade View (Kepatuhan Penuh Lineone UI Kit)

Buat file `resources/views/livewire/tools/{kebab-case}.blade.php`:
- Terapkan 3 state tampilan (Upload Dropzone, Pengaturan/Pratinjau, Hasil Download).
- **Tipografi**: Gunakan `font-semibold` atau `font-medium`. DILARANG menggunakan `font-black`, `font-extrabold`, atau arbitrary font sizes seperti `text-[9px]`, `text-[10px]`, `text-[11px]`. Gunakan token standar `text-xs`, `text-sm`, `text-base`.
- **Tombol**: Gunakan `btn h-9 rounded-full font-medium text-xs px-5 shadow-xs`. Dilarang efek glow berpendar berwarna (`shadow-primary/*`).
- **Badges**: Gunakan `badge rounded-full px-2.5 py-0.5 text-xs font-medium` dengan palet soft Lineone (`bg-primary/10 text-primary`, `bg-secondary/15 text-secondary`, `bg-warning/15 text-warning`, `bg-success/15 text-success`, `bg-slate-150 text-slate-700`). DILARANG KERAS menyisipkan emoji (`🌟`, `👑`, `🔥`, `✨`), icon `<x-lucide-...>`, atau gradient buatan di dalam badge.
- **Anti-AI-Slop**: Dilarang menambahkan bola ambient blur melayang (`filter blur-3xl opacity-20 bg-linear-to-r`) atau border pelangi. Semua tampilan wajib bersih dan modern sesuai Lineone UI Kit.

---

## Langkah 6: Pembaruan Pengetahuan Otak AI (Mental Model & Skill)

Setiap kali menyelesaikan penambahan tool baru, agen **WAJIB** memperbarui dokumentasi pengetahuan proyek:
1. Tambahkan baris tool baru ke tabel katalog di [`.agents/knowledge/system_architecture_map.md`](file:///c:/laragon/www/toolbox/.agents/knowledge/system_architecture_map.md).
2. Jika tool memperkenalkan teknik baru (misal: enkripsi AES, manipulasi Pillow opacity, atau morph rotation), catat polanya ke [`.agents/knowledge/codebase_mental_model.md`](file:///c:/laragon/www/toolbox/.agents/knowledge/codebase_mental_model.md).
3. Bersihkan dan kompilasi template Blade:
   ```powershell
   php artisan view:clear; php artisan view:cache
   npm run build
   ```
