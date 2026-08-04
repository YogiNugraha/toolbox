# 🛡️ HARDENING BRIEF — Audit Keamanan & Reliability

> Tujuan dokumen ini: pastikan hal-hal yang sudah direncanakan di `PROJECT_SPEC.md` (section 8) dan addendum auth (section 6) **benar-benar terpasang**, bukan cuma direncanakan. Kerjakan dengan pola: **AUDIT dulu, baru FIX** — untuk tiap poin di bawah, cek dulu kondisi sekarang di kode, laporkan status (Sudah ✅ / Belum ❌ / Sebagian ⚠️), baru perbaiki yang belum/kurang.

---

## 1. Retensi & Cleanup File Otomatis

**Cek:**
- Apakah ada scheduled command yang jalan otomatis (bukan cuma file `CleanupTempFiles.php` yang dibuat tapi tidak pernah didaftarkan)?
- Apakah command itu benar terdaftar di `routes/console.php` dengan `Schedule::command(...)->hourly()` (Laravel 11/12 style)?
- Apakah `FILE_RETENTION_HOURS` di `.env` benar dipakai di dalam command, bukan hardcode angka?

**Fix kalau belum ada/belum jalan:**
```php
// routes/console.php
use Illuminate\Support\Facades\Schedule;

Schedule::command('cleanup:temp-files')->hourly();
```
```php
// app/Console/Commands/CleanupTempFiles.php — logic inti
public function handle()
{
    $retentionHours = (int) env('FILE_RETENTION_HOURS', 24);
    $cutoff = now()->subHours($retentionHours);

    $expired = \App\Models\Activity::where('created_at', '<', $cutoff)
        ->whereNotNull('result_path')
        ->get();

    foreach ($expired as $activity) {
        if ($activity->result_path && \Storage::exists($activity->result_path)) {
            \Storage::delete($activity->result_path);
        }
        $activity->update(['result_path' => null, 'status' => 'expired']);
    }

    $this->info("Cleaned up {$expired->count()} expired files.");
}
```
**Penting:** karena scheduler Laravel butuh cron/task scheduler jalan tiap menit di background — untuk local dev di Laragon, ini biasanya TIDAK otomatis jalan kecuali ada proses `php artisan schedule:work` yang dijalankan manual di terminal terpisah, atau di-setup sebagai scheduled task Windows. Kalau agent belum setup ini, kasih tahu saya caranya jalankan `schedule:work` supaya saya tahu harus buka terminal tambahan.

---

## 2. Otorisasi Download (Kepemilikan File)

**Cek:**
- Buka route/controller yang serve download file hasil proses. Apakah ada pengecekan `$activity->user_id === auth()->id()` sebelum file diserve?
- Coba skenario: user login sebagai A, catat URL download salah satu hasil proses, logout, login sebagai user B, akses URL yang sama — apakah ketolak (403) atau malah bisa download?

**Fix kalau belum ada:**
```php
Route::get('/download/{activity}', function (\App\Models\Activity $activity) {
    abort_if($activity->user_id !== auth()->id(), 403, 'Anda tidak memiliki akses ke file ini.');
    abort_if(!$activity->result_path || !\Storage::exists($activity->result_path), 404, 'File sudah tidak tersedia (mungkin sudah kedaluwarsa).');

    return \Storage::download($activity->result_path, $activity->original_filename);
})->middleware('auth')->name('activity.download');
```
Pastikan juga tombol Download di halaman Riwayat/hasil compress memang mengarah ke route terproteksi ini, bukan langsung ke URL publik `storage/...` yang bisa ditebak siapa saja.

---

## 3. Validasi File Upload

**Cek tiap tools (Compress Gambar, Convert Format Gambar, PDF ke Word):**
- Apakah validasi cuma cek ekstensi (`mimes:jpg,png`) atau juga cek MIME type asli dari isi file?
- Apakah ada batas ukuran file yang ditegakkan di server (bukan cuma di UI/JS)?
- Apakah `upload_max_filesize` & `post_max_size` di `php.ini` Laragon sudah disesuaikan (minimal 10-20MB) supaya validasi di Laravel benar-benar yang menentukan, bukan keblokir duluan sama PHP dengan pesan error yang membingungkan?

**Fix — contoh rule Livewire yang lebih ketat:**
```php
public function rules()
{
    return [
        'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'], // max dalam KB
    ];
}

// Tambahan cek manual MIME asli (bukan cuma ekstensi):
$realMime = $this->file->getMimeType();
if (! in_array($realMime, ['image/jpeg', 'image/png', 'image/webp'])) {
    $this->addError('file', 'Format file tidak valid.');
    return;
}
```

---

## 4. Rate Limiting

**Cek:**
- Apakah route tools & auth (login/register) sudah dibungkus middleware `throttle`?

**Fix:**
```php
// login/register — cegah brute force
Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    Route::get('/login', ...);
    Route::get('/register', ...);
});

// tools — cegah spam upload
Route::middleware(['auth', 'throttle:20,1'])->group(function () {
    // semua route tools
});
```
Sesuaikan angka kalau terlalu ketat/longgar buat pemakaian normal.

---

## 5. Queue untuk PDF to Word — Status Harus Terlihat Jelas

**Cek:**
- Buka `.env`, apakah `QUEUE_CONNECTION=database` (queue asli) atau masih `sync`?
- Kalau `database`: apakah ada proses `php artisan queue:work` yang perlu dijalankan manual? (Kalau iya, beri tahu saya supaya saya buka terminal tambahan untuk itu.)
- Di UI, apakah user melihat status "Memproses..." yang jelas selama job berjalan (pakai `wire:poll`), atau tampilan diam saja seolah nge-freeze?

**Fix kalau status belum kelihatan di UI:**
```blade
<div wire:poll.2s="checkStatus">
    @if($status === 'processing')
        <div class="flex items-center gap-2 text-ink-muted font-mono text-sm">
            <span class="animate-spin">⏳</span> Memproses dokumen...
        </div>
    @elseif($status === 'completed')
        <a href="{{ route('activity.download', $activity) }}" class="...">Download Hasil</a>
    @elseif($status === 'failed')
        <p class="text-red-600 text-sm">Konversi gagal. Coba lagi atau gunakan file lain.</p>
    @endif
</div>
```

---

## 6. Penanganan Error / Edge Case

**Cek & pastikan ada pesan error yang jelas (bukan crash/500 blank page) untuk skenario:**
- File PDF corrupt/rusak saat mau dikonversi ke Word
- LibreOffice gagal jalan (path salah, proses timeout) — jangan biarkan job gantung selamanya di status `processing`, kasih timeout dan update ke `failed` kalau lewat waktu
- Upload gambar yang sebenarnya bukan gambar valid (file di-rename jadi `.jpg` padahal isinya bukan gambar)
- User upload file kosong (0 KB) atau melebihi batas ukuran

**Prinsip:** setiap kegagalan proses harus (1) update `status` activity jadi `failed` — jangan biarkan menggantung di `processing`, dan (2) tampilkan pesan singkat & jelas ke user, bukan generic error Laravel atau halaman putih.

```php
try {
    // proses convert/compress
} catch (\Throwable $e) {
    $activity->update(['status' => 'failed']);
    \Log::error('Tool processing failed', ['activity_id' => $activity->id, 'error' => $e->getMessage()]);
    $this->addError('file', 'Gagal memproses file. Pastikan file tidak rusak dan coba lagi.');
    return;
}
```

---

## 7. Laporan yang Diminta dari Agent

Setelah audit, tolong laporkan dalam format tabel singkat seperti ini sebelum mulai memperbaiki:

| Item | Status Sekarang | Perlu Diperbaiki? |
|---|---|---|
| Cleanup file otomatis | ✅/⚠️/❌ | ... |
| Otorisasi download | ✅/⚠️/❌ | ... |
| Validasi file (MIME asli) | ✅/⚠️/❌ | ... |
| Rate limiting | ✅/⚠️/❌ | ... |
| Queue PDF to Word + status UI | ✅/⚠️/❌ | ... |
| Error handling edge case | ✅/⚠️/❌ | ... |

---

## 8. Definition of Done

- [x] File temp otomatis terhapus sesuai `FILE_RETENTION_HOURS`, dan cara menjalankan scheduler di local (Laragon) sudah jelas
- [x] User tidak bisa download file milik user lain (terverifikasi dengan tes 2 akun)
- [x] Upload file dengan ekstensi dipalsukan (misal `.txt` di-rename jadi `.jpg`) ditolak sistem
- [x] Login/register punya rate limit, tidak bisa di-brute-force tanpa batas
- [x] Status "Memproses..." kelihatan jelas di UI selama job PDF to Word berjalan
- [x] Semua skenario gagal (file corrupt, LibreOffice error, timeout) menghasilkan pesan error yang jelas ke user, bukan crash/hang
- [x] Tidak ada activity yang menggantung selamanya di status `processing`
