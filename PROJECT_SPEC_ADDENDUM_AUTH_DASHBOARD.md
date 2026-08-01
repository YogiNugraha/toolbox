# 🔐 ADDENDUM SPEC — Auth & Dashboard Restructure (ToolBox)

> Dokumen ini adalah **lanjutan/perubahan** dari `PROJECT_SPEC.md` sebelumnya, bukan project baru dari nol. Baca dua-duanya. Kalau ada bagian yang bertentangan (khususnya soal "homepage publik"), **dokumen ini yang menang** — sistem berubah dari public tools site menjadi dashboard privat dengan login.

---

## 0. Konteks Perubahan

Sebelumnya: siapa saja bisa langsung pakai tools tanpa login, homepage berupa grid tools publik.

**Sekarang:** seluruh sistem jadi **privat**, wajib login untuk pakai tools apapun, dan setiap pemakaian tools **direkam** ke akun user yang bersangkutan (riwayat pemakaian, statistik).

Kalau di project sudah ada progress dari `PROJECT_SPEC.md` sebelumnya, jangan bongkar ulang dari nol — refactor bagian yang perlu (routing, homepage jadi dashboard, tambah auth), pertahankan yang sudah jalan (Livewire component tools, service class processing, dll).

---

## 1. Ringkasan Perubahan Utama

1. **Login & Register** — wajib punya akun untuk akses sistem.
2. **Dashboard, bukan public homepage** — setelah login, user masuk ke dashboard (statistik + akses tools), bukan grid tools terbuka.
3. **Activity log / riwayat** — setiap kali user pakai tools (compress, convert, dll), tercatat: tools apa, kapan, ukuran file sebelum/sesudah — bisa dilihat lagi di halaman "Riwayat".
4. Semua route tools & dashboard **diproteksi middleware `auth`** — tamu (belum login) otomatis dilempar ke halaman login.

---

## 2. Perubahan Arsitektur — Public → Private Dashboard

| | Sebelum | Sesudah |
|---|---|---|
| Route `/` | Grid tools publik | Redirect: kalau belum login → `/login`, kalau sudah login → `/dashboard` |
| Akses tools | Bebas tanpa login | Wajib login (`auth` middleware) |
| Struktur halaman utama | Homepage tools | Dashboard (statistik + shortcut tools + riwayat terakhir) |
| Riwayat pemakaian | Tidak ada | Ada, per user, halaman `/history` |

### Routing (contoh struktur `routes/web.php`)

```php
use Illuminate\Support\Facades\Route;

// Redirect root sesuai status login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// GUEST ONLY (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
    Route::get('/register', \App\Livewire\Auth\Register::class)->name('register');
});

// AUTH ONLY (wajib login)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard\Overview::class)->name('dashboard');
    Route::get('/history', \App\Livewire\Dashboard\History::class)->name('history');
    Route::get('/profile', \App\Livewire\Dashboard\Profile::class)->name('profile');

    Route::get('/tools/{slug}', \App\Livewire\Tools\ToolPage::class)->name('tools.show');
    // atau tetap route per-tool terpisah kalau lebih gampang, sesuai struktur yang sudah ada di PROJECT_SPEC.md

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
```

> Catatan: penamaan class Livewire di atas contoh saja, sesuaikan dengan konvensi yang sudah dipakai di project (tetap ikuti aturan Livewire class-based/`--class` di `PROJECT_SPEC.md` bagian 3).

---

## 3. Autentikasi (Login & Register)

**Jangan install starter kit penuh** (Breeze/Jetstream) — itu akan menimpa struktur project yang sudah dibangun manual. Cukup pakai fitur auth bawaan Laravel (`Illuminate\Support\Facades\Auth`) + bikin UI-nya sendiri pakai Livewire component gaya v3 (class-based, sesuai `PROJECT_SPEC.md` bagian 3).

### Component yang perlu dibuat
```bash
php artisan make:livewire Auth/Login --class
php artisan make:livewire Auth/Register --class
```

### Login — logic inti
```php
public function login()
{
    $this->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (! auth()->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
        $this->addError('email', 'Email atau password salah.');
        return;
    }

    request()->session()->regenerate();
    return redirect()->route('dashboard');
}
```

### Register — logic inti
```php
public function register()
{
    $this->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
    ]);

    $user = \App\Models\User::create([
        'name' => $this->name,
        'email' => $this->email,
        'password' => bcrypt($this->password),
    ]);

    auth()->login($user);
    return redirect()->route('dashboard');
}
```

- Tabel `users` bawaan Laravel sudah cukup (name, email, password) — tidak perlu ubah struktur kolom kecuali mau nambah field opsional (misal `avatar`).
- Tampilan login/register: form simpel, centered card, tidak perlu sidebar (halaman ini di luar layout dashboard).

---

## 4. Database — Mencatat Aktivitas User

Bikin tabel baru `activities` untuk merekam setiap pemakaian tools.

```bash
php artisan make:model Activity -m
```

**Migration:**
```php
Schema::create('activities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('tool_slug');           // 'compress-image', 'pdf-to-word', dst — cocokkan dengan config/tools.php
    $table->string('original_filename');
    $table->unsignedBigInteger('original_size')->nullable();  // dalam bytes
    $table->unsignedBigInteger('result_size')->nullable();    // dalam bytes
    $table->string('status')->default('processing'); // processing | completed | failed
    $table->string('result_path')->nullable();        // lokasi file hasil (buat download ulang)
    $table->json('meta')->nullable();                 // simpan detail tambahan, misal preset yang dipakai
    $table->timestamps();
});
```

**Model `Activity`:**
```php
class Activity extends Model
{
    protected $fillable = [
        'user_id', 'tool_slug', 'original_filename',
        'original_size', 'result_size', 'status', 'result_path', 'meta',
    ];
    protected $casts = ['meta' => 'array'];

    public function user() { return $this->belongsTo(User::class); }
}
```

**Model `User`** tambahkan relasi:
```php
public function activities() { return $this->hasMany(Activity::class); }
```

---

## 5. Integrasi Activity Log ke Tiap Tools

Setiap Livewire tools component (ImageCompressor, ImageConverter, PdfToWord, dst) **wajib** mencatat ke tabel `activities` setiap kali diproses:

```php
// contoh di dalam method proses ImageCompressor
$activity = Activity::create([
    'user_id' => auth()->id(),
    'tool_slug' => 'compress-image',
    'original_filename' => $this->file->getClientOriginalName(),
    'original_size' => $this->file->getSize(),
    'status' => 'processing',
]);

// ...proses compress via ImageProcessorService...

$activity->update([
    'result_size' => $resultSizeBytes,
    'result_path' => $resultPath,
    'status' => 'completed',
    'meta' => ['preset' => $this->selectedPreset, 'quality' => $this->quality],
]);
```

Kalau proses gagal, update `status` jadi `failed` (jangan biarkan record menggantung di `processing` selamanya).

Untuk job async (PDF to Word), pola sama, cuma update status-nya dilakukan di dalam Job setelah proses LibreOffice selesai.

---

## 6. File Storage & Retensi — Sekarang per User

Karena sekarang tiap file punya pemilik yang jelas (user login), ubah lokasi penyimpanan supaya terorganisir per user dan aman:

- Sebelumnya: `storage/app/private/temp/{uuid}.ext`
- Sekarang: `storage/app/private/users/{user_id}/{uuid}.ext`

**Retensi diperpanjang** (karena sekarang ada histori & pemilik yang jelas, tidak perlu buru-buru dihapus seperti sistem anonim sebelumnya). Tambahkan di `.env`:
```
FILE_RETENTION_HOURS=24
```

Update scheduled command cleanup (`CleanupTempFiles`) supaya:
1. Hapus file fisik yang lebih tua dari `FILE_RETENTION_HOURS`.
2. Update `status` activity terkait jadi `expired` (atau kosongkan `result_path`) supaya halaman Riwayat tahu file itu sudah tidak bisa didownload lagi, dan menampilkan label "File sudah kedaluwarsa" alih-alih tombol download rusak.

**Keamanan download** — WAJIB cek kepemilikan sebelum serve file:
```php
Route::get('/download/{activity}', function (Activity $activity) {
    abort_if($activity->user_id !== auth()->id(), 403);
    abort_if(!$activity->result_path || !Storage::exists($activity->result_path), 404);

    return Storage::download($activity->result_path, $activity->original_filename);
})->middleware('auth')->name('activity.download');
```

---

## 7. Struktur Dashboard (UI)

### Layout baru: `resources/views/layouts/dashboard.blade.php`
- **Sidebar** (collapsible di mobile): logo, menu — Dashboard, semua kategori Tools (dari `config/tools.php`, sama seperti dulu tapi sekarang di dalam sidebar bukan grid publik), Riwayat, Profil, tombol Logout di paling bawah.
- **Topbar**: nama user + avatar (inisial nama kalau belum ada foto), dropdown kecil (Profil / Logout).

> Kalau desain "Digital Workbench / Blueprint" (palet warna, tipografi monospace untuk angka, dst) yang sudah didiskusikan sebelumnya sudah diterapkan di project, lanjutkan gaya yang sama di sidebar & dashboard ini biar konsisten. Kalau belum sempat, styling Tailwind standar dulu tidak masalah — prioritaskan fungsionalitas jalan dulu, estetika belakangan.

### Halaman `/dashboard` (Overview)
- Sapaan singkat: "Halo, {nama user}"
- 2-3 kartu statistik ringkas: total file diproses, total penghematan ukuran (akumulasi `original_size - result_size` dari semua activity user ini), tools paling sering dipakai
- Grid/list shortcut ke semua tools (masih dari `config/tools.php`, sekarang ditampilkan di dalam dashboard, bukan di homepage publik)
- List 5 aktivitas terakhir, dengan link "Lihat semua riwayat" ke halaman `/history`

### Halaman `/history` (Riwayat)
- Tabel/list semua activity milik user yang login **saja** (jangan sampai user A bisa lihat riwayat user B — filter selalu pakai `auth()->id()`)
- Kolom: Tools, Nama file, Ukuran asli → hasil, Persentase hemat, Tanggal, Status, Aksi (tombol Download kalau masih dalam masa retensi, atau label "Kedaluwarsa" kalau sudah lewat)
- Filter/sort sederhana (opsional): berdasarkan tools atau tanggal

### Halaman `/profile`
- Form sederhana: ubah nama, ubah password (minta password lama untuk konfirmasi)

---

## 8. Command Cheat Sheet Tambahan

```bash
# Auth components
php artisan make:livewire Auth/Login --class
php artisan make:livewire Auth/Register --class

# Dashboard components
php artisan make:livewire Dashboard/Overview --class
php artisan make:livewire Dashboard/History --class
php artisan make:livewire Dashboard/Profile --class

# Model + migration activity log
php artisan make:model Activity -m
php artisan migrate
```

---

## 9. Definition of Done (tambahan, digabung dengan checklist di `PROJECT_SPEC.md`)

- [ ] User baru bisa Register, otomatis login setelah daftar
- [ ] User bisa Login & Logout, session tersimpan dengan benar
- [ ] Tamu (belum login) yang coba akses `/dashboard`, `/history`, atau route tools manapun otomatis dilempar ke `/login`
- [ ] Root `/` mengarahkan otomatis sesuai status login (ke `/login` atau `/dashboard`)
- [ ] Setiap pemakaian tools (compress, convert, pdf to word) tercatat di tabel `activities` dengan `user_id` yang benar
- [ ] Halaman `/history` hanya menampilkan riwayat milik user yang sedang login, tidak bocor ke user lain
- [ ] Statistik di halaman Dashboard (total file, total penghematan) sesuai dengan data activity yang sebenarnya
- [ ] Download file dari Riwayat menolak akses kalau bukan pemilik file (cek 403) atau file sudah kedaluwarsa (cek 404 + pesan jelas)
- [ ] Scheduled cleanup tetap jalan, sekarang dengan window retensi `FILE_RETENTION_HOURS` dan otomatis update status activity jadi `expired`
- [ ] Sidebar dashboard responsive & bisa di-collapse di mobile

---

## 10. Catatan untuk Agent

- Ini refactor di atas project yang sudah ada — jangan hapus/tulis ulang service class processing (`ImageProcessorService`, `PdfConverterService`) yang sudah jalan, cukup tambahkan pemanggilan `Activity::create()`/`update()` di titik yang tepat.
- Urutan pengerjaan yang disarankan: (1) Auth (login/register + middleware routing) dulu sampai bisa login-logout dengan benar → (2) tabel & model `Activity` → (3) integrasikan activity log ke tools yang sudah ada satu per satu → (4) baru bangun halaman Dashboard Overview & History → (5) terakhir styling/UI polish sidebar.
- Kalau ada keputusan yang tidak dijelaskan detail di sini (misal: apakah registrasi terbuka untuk umum atau perlu approval admin), asumsikan **registrasi terbuka bebas** dulu (paling simpel), catat sebagai asumsi di README — bisa dibatasi belakangan kalau perlu.
