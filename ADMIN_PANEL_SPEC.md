# 🛠️ ADDENDUM SPEC — Admin Panel

> Lanjutan dari `HANDOVER_DOC.md` poin 5 (Tech Debt). Fokus MVP: (1) lihat total pengguna, (2) lihat revenue, (3) ban/unban user. Jangan melebar ke fitur lain dulu (role hierarchy, audit log, dll) — proporsional sesuai kebutuhan yang diminta.

---

## 1. Database — Tambahan Kolom di `users`

```php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_admin')->default(false);
    $table->timestamp('banned_at')->nullable();
});
```

**Cara menjadikan diri sendiri admin (tidak ada UI untuk ini — sengaja, demi keamanan):**
```bash
php artisan tinker
>>> User::where('email', 'emailkamu@gmail.com')->update(['is_admin' => true]);
```

---

## 2. ⚠️ PENTING — Perbaikan Cara Hitung Revenue Sebelum Dipakai di Admin Panel

Status `expired` di tabel `subscriptions` saat ini dipakai untuk 2 kondisi berbeda:
- **Sudah bayar, lalu 30 harinya habis** → ini pendapatan asli, HARUS dihitung
- **Checkout ditinggal, tidak pernah dibayar, auto-expired oleh sistem** (dari `FRONTEND_CHECKOUT_POLISH.md` section 5) → ini BUKAN pendapatan, JANGAN dihitung

**Jangan hitung revenue hanya berdasarkan status.** Hitung berdasarkan bukti transaksi beneran terjadi:
```php
// query revenue yang BENAR — cek starts_at atau midtrans_transaction_id, bukan status
$totalRevenue = Subscription::whereNotNull('midtrans_transaction_id')->sum('amount');

$revenueThisMonth = Subscription::whereNotNull('midtrans_transaction_id')
    ->whereMonth('starts_at', now()->month)
    ->whereYear('starts_at', now()->year)
    ->sum('amount');
```
`midtrans_transaction_id` cuma keisi kalau webhook Midtrans benar-benar konfirmasi pembayaran — jadi ini penanda paling akurat, lebih baik daripada mengandalkan `status`.

---

## 3. Middleware

### A. `IsAdmin` — proteksi route admin
```bash
php artisan make:middleware IsAdmin
```
```php
public function handle($request, Closure $next)
{
    abort_unless(auth()->check() && auth()->user()->is_admin, 403);
    return $next($request);
}
```

### B. `EnsureUserIsNotBanned` — cek di SETIAP request, bukan cuma pas login
Ini penting: kalau user sedang banned SAAT sesi masih aktif (misal admin nge-ban sementara user itu masih login di device lain), harus langsung ketolak di request berikutnya, tidak perlu tunggu logout manual.
```bash
php artisan make:middleware EnsureUserIsNotBanned
```
```php
public function handle($request, Closure $next)
{
    if (auth()->check() && auth()->user()->banned_at) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('error', 'Akun kamu telah dinonaktifkan.');
    }
    return $next($request);
}
```
Daftarkan middleware ini di GROUP `auth` yang sudah dipakai semua route dashboard/tools (bukan cuma di route admin) — supaya user yang di-ban langsung ke-logout otomatis di request berikutnya, dari halaman manapun dia berada.

---

## 4. Routes

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', \App\Livewire\Admin\Overview::class)->name('overview');
    Route::get('/users', \App\Livewire\Admin\Users::class)->name('users');
    Route::get('/transactions', \App\Livewire\Admin\Transactions::class)->name('transactions');
});
```

---

## 5. Halaman Admin Overview (`/admin`)

Kartu statistik ringkas (style sama seperti dashboard user — hairline border, angka pakai `font-mono`):

| Kartu | Query |
|---|---|
| Total Pengguna | `User::count()` |
| Pengguna Pro Aktif | `User::whereHas('subscriptions', fn($q) => $q->where('status','active')->where('expires_at','>',now()))->count()` |
| Total Revenue (all-time) | Query section 2 |
| Revenue Bulan Ini | Query section 2 |

Di bawahnya: tabel 10 transaksi terakhir (dari semua user, bukan cuma yang login), kolom: User, Order ID, Nominal, Status, Tanggal.

---

## 6. Halaman Manajemen User (`/admin/users`)

- Tabel semua user: Nama, Email, Status Plan (Free/Pro + tanggal expired kalau Pro), Total belanja user itu (sum dari `midtrans_transaction_id` tidak null miliknya), Tanggal daftar, Status akun (Aktif/Banned)
- Search by nama/email
- Filter by status plan (Semua/Free/Pro) dan status akun (Semua/Aktif/Banned)
- Pagination (jangan load semua user sekaligus kalau datanya banyak nanti)

### Aksi Ban/Unban — pakai Livewire Alert buat konfirmasi (sudah terpasang)
```php
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

public function confirmBan($userId)
{
    LivewireAlert::title('Ban pengguna ini?')
        ->text('Pengguna tidak akan bisa login sampai di-unban lagi.')
        ->warning()
        ->withConfirmButton('Ya, Ban')
        ->withCancelButton('Batal')
        ->onConfirm('banUser', ['userId' => $userId])
        ->show();
}

public function banUser($userId)
{
    \App\Models\User::where('id', $userId)->update(['banned_at' => now()]);
    LivewireAlert::title('Pengguna telah di-ban.')->success()->show();
}

public function unbanUser($userId)
{
    \App\Models\User::where('id', $userId)->update(['banned_at' => null]);
    LivewireAlert::title('Pengguna telah di-unban.')->success()->show();
}
```
> Catatan: sesuaikan syntax `onConfirm` dengan API v4 yang sudah dipakai di `LIVEWIRE_ALERT_INTEGRATION.md` — cek dokumentasi resmi kalau ada perbedaan method, karena package ini masih sering update.

**Proteksi tambahan:** admin tidak boleh bisa ban dirinya sendiri (jaga-jaga kekunci akses):
```php
abort_if($userId === auth()->id(), 403, 'Tidak bisa ban diri sendiri.');
```

---

## 7. Halaman Transaksi (`/admin/transactions`)

- Tabel semua transaksi dari semua user: User, Order ID, Nominal, Status, Tanggal, Channel pembayaran (kalau datanya ada dari Midtrans)
- Filter by status & rentang tanggal
- Summary di atas tabel: Total Revenue, Jumlah Transaksi Sukses, Jumlah Pending, Jumlah Gagal/Expired asli (bukan yang ditinggal)

---

## 8. UI — Konsistensi & Pembeda Area Admin

- Reuse token desain yang sama (ink, paper, amber, hairline, font-mono untuk angka) — JANGAN bikin tema baru yang beda sendiri.
- Layout admin boleh pakai sidebar terpisah (`layouts/admin.blade.php`) dengan menu: Overview, Pengguna, Transaksi — supaya jelas beda konteks dari dashboard user biasa.
- Link ke `/admin` HANYA muncul di dropdown profil kalau `auth()->user()->is_admin === true` — jangan sampai muncul buat user biasa meskipun cuma link yang nanti 403 kalau diklik (tetap sembunyikan link-nya).

---

## 9. Definition of Done

- [ ] User dengan `is_admin = false` yang coba akses `/admin` manapun mendapat 403, bukan malah bisa masuk
- [ ] Angka revenue di Admin Overview TIDAK ikut menghitung checkout yang ditinggal/tidak pernah dibayar (dicek manual: bikin 1 checkout, tinggalkan sampai auto-expired, pastikan revenue tidak berubah)
- [ ] Ban user → user itu langsung ter-logout di request berikutnya meskipun sedang login aktif, tidak perlu tunggu dia logout manual
- [ ] Admin tidak bisa ban dirinya sendiri
- [ ] Unban mengembalikan akses login seperti biasa
- [ ] Link ke Admin Panel cuma muncul untuk akun yang memang admin
- [ ] Tabel user & transaksi punya pagination, tidak nge-load semua data sekaligus
