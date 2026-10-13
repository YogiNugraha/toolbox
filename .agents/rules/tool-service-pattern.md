# Tool Service Pattern & Engine Standards: ToolBox

Dokumen ini menjelaskan pola arsitektur pembuatan dan pemeliharaan alat (tools) di ToolBox.

## 1. Alur Pembuatan Tool Baru (4-Tier Pipeline)

### Tahap 1: Registrasi Konfigurasi (`config/tools.php`)
Tambahkan definisi item baru pada array:
```php
[
    'slug' => 'sample-tool',
    'name' => 'Sample Tool',
    'description' => 'Deskripsi singkat fungsionalitas tool.',
    'icon' => 'wrench', // Lucide icon identifier
    'category' => 'Document', // 'Document' atau 'Image'
    'component' => 'tools.sample-tool', // Livewire component name
    'lockable_features' => [
        'feature_key' => 'Kunci Fitur X (Khusus Pro)',
    ],
],
```
Jalankan sinkronisasi via Tinker atau method `\App\Models\Tool::syncFromConfig()`.

---

### Tahap 2: Backend Processing Service (`app/Services/Tools/{Name}Service.php`)
Aturan pembuatan Service:
1. Simpan berkas hasil olahan pada path `users/{userId}/{outputFilename}` di disk `local` (`storage/app/private`).
2. Buat folder tujuan terlebih dahulu: `Storage::disk('local')->makeDirectory($relativeDir);`.
3. Panggil skrip Python di `storage/scripts/{script}.py` via `Symfony\Component\Process\Process`:
   ```php
   $pythonScript = base_path('storage/scripts/sample_tool.py');
   $pythonExecutable = config('services.python_path', 'python');
   
   $process = new Process([
       $pythonExecutable,
       $pythonScript,
       $inputPath,
       $outputPath,
       // parameter tambahan
   ]);
   $process->setTimeout(120);
   $process->run();
   ```
4. Verifikasi kembalian JSON dari skrip:
   ```php
   $output = trim($process->getOutput());
   if (preg_match('/\{.*\}/s', $output, $matches)) {
       $data = json_decode($matches[0], true);
       if (($data['status'] ?? '') === 'success') {
           return [
               'result_path' => $relativeResultPath,
               'result_filename' => $finalFilename,
               'original_size' => $data['original_size'],
               'new_size' => $data['new_size'],
           ];
       }
   }
   throw new \Exception("Gagal memproses berkas: " . ($process->getErrorOutput() ?: $output));
   ```

---

### Tahap 3: Skrip Pemrosesan Python (`storage/scripts/*.py`)
Aturan pembuatan skrip Python:
1. **Header Site Packages Windows**:
   Wajib sertakan blok pencarian direktori site-packages agar modul pihak ketiga dapat dimuat saat dipanggil oleh web server:
   ```python
   import sys, os, site
   _potential_site_dirs = [
       os.path.expanduser(r"~\AppData\Roaming\Python\Python313\site-packages"),
       r"C:\Users\Yogi Nugraha\AppData\Roaming\Python\Python313\site-packages",
       r"C:\Python313\Lib\site-packages",
       r"C:\laragon\bin\python\python-3.10\Lib\site-packages",
   ]
   for _dir in _potential_site_dirs:
       if os.path.exists(_dir) and _dir not in sys.path:
           site.addsitedir(_dir)
   ```
2. **Output Format**:
   Kembalikan respons murni dalam format JSON ke `stdout`:
   - Sukses: `{"status": "success", "original_size": 1024, "new_size": 512, "output_path": "..."}`
   - Error: `{"status": "error", "message": "Penyebab kegagalan"}` lalu `sys.exit(1)`.

---

### Tahap 4: Livewire Component & Activity Logging (`app/Livewire/Tools/{Name}.php`)
Aturan pembuatan controller Livewire:
1. **Entitlement & Quota Check**:
   ```php
   $user = auth()->user();
   $toolSlug = 'sample-tool';
   
   $remaining = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
   if ($remaining !== null && $remaining <= 0) {
       $this->errorMsg = 'Kuota bulanan Anda sudah habis. Silakan upgrade ke paket Pro.';
       $this->toast('Kuota habis.', 'info');
       return;
   }
   ```
2. **Activity Recording Lifecycle**:
   ```php
   $activity = Activity::create([
       'user_id' => auth()->id(),
       'tool_slug' => $toolSlug,
       'original_filename' => $this->file->getClientOriginalName(),
       'original_size' => $this->file->getSize(),
       'status' => 'processing',
   ]);
   
   try {
       $result = $service->process($this->file);
       
       $activity->update([
           'result_filename' => $result['result_filename'],
           'result_size' => $result['new_size'],
           'result_path' => $result['result_path'],
           'status' => 'completed',
       ]);
       
       \App\Models\Tool::incrementUsage($toolSlug);
       $this->toast('Proses berhasil diselesaikan!', 'success');
   } catch (\Exception $e) {
       $activity->update([
           'status' => 'failed',
           'meta' => ['error' => $e->getMessage()],
       ]);
       $this->errorMsg = $e->getMessage();
       $this->toast('Proses gagal: ' . $e->getMessage(), 'error');
   }
   ```
3. **Download Action**:
   Periksa keberadaan berkas via `Storage::disk('local')->exists($this->resultPath)` dan kembalikan response download dengan nama berkas hasil.
