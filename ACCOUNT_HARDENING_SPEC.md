# 🔐 ADDENDUM SPEC — Account Hardening (Verifikasi Email, Reset Password, Single Session)

> Lanjutan dari sistem auth yang sudah ada (`PROJECT_SPEC_ADDENDUM_AUTH_DASHBOARD.md`). Fokus: pastikan identitas akun valid, ada jalur recovery kalau lupa password, dan satu akun cuma bisa aktif di satu sesi.

---

## 1. Verifikasi Email (Poin 1 & 2 — digabung, ini 1 fitur yang sama)

Laravel sudah punya sistem verifikasi email bawaan — tinggal diaktifkan, tidak perlu bikin dari nol.

### A. Model `User` — implement `MustVerifyEmail`

```php
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    // ...
}
```

Cek dulu apakah kolom `email_verified_at` sudah ada di tabel `users` (biasanya sudah ada dari migration default Laravel) — kalau belum, tambahkan lewat migration.

### B. Trigger email verifikasi saat register

Di method `register()` pada `Auth/Register.php`, setelah user berhasil dibuat:

```php
use Illuminate\Auth\Events\Registered;

$user = User::create([...]);
event(new Registered($user)); // ini yang otomatis kirim email verifikasi
auth()->login($user);
return redirect()->route('dashboard');
```

Pastikan listener `SendEmailVerificationNotification` terdaftar (biasanya otomatis lewat `EventServiceProvider` bawaan Laravel — cek `bootstrap/app.php` atau `EventServiceProvider` kalau di project ini strukturnya dimodifikasi manual).

### C. Wajibkan verifikasi sebelum bisa pakai dashboard/tools

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // semua route dashboard & tools yang SUDAH ADA, tambahkan 'verified'
});
```

User yang belum verifikasi otomatis diarahkan ke halaman notice verifikasi (route `verification.notice` bawaan Laravel) — buat Livewire component untuk halaman ini, style sesuai design system:

```php
Route::get('/email/verify', \App\Livewire\Auth\VerifyEmailNotice::class)
    ->middleware('auth')->name('verification.notice');
```

Isi halaman: "Cek email kamu untuk link verifikasi" + tombol "Kirim Ulang Email" (panggil `$request->user()->sendEmailVerificationNotification();`).

### D. REVISI — Ganti Email Pakai Pola "Pending", Bukan Update Langsung

> ⚠️ Ini menggantikan pendekatan sebelumnya (update langsung lalu reset verifikasi). Pendekatan lama punya celah: user bisa asal ketik email apapun dan itu LANGSUNG jadi email akun, verifikasi cuma menyusul belakangan. Pendekatan baru ini menahan perubahan sampai kepemilikan email baru benar-benar terbukti.

**Prinsip:** kolom `users.email` TIDAK berubah sama sekali sampai user klik link konfirmasi yang dikirim ke alamat BARU. Selama belum diklik, email akun tetap yang lama (dan tetap berstatus terverifikasi seperti biasa — tidak ada jeda "akun jadi unverified" sama sekali).

#### Kolom baru

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('pending_email')->nullable();
});
```

#### Logic di `Profile.php` — JANGAN update `email` langsung

```php
public function save()
{
    $user = auth()->user();
    $emailChanged = $this->email !== $user->email;

    $user->update([
        'name' => $this->name,
        'phone' => $this->phone,
        // 'email' SENGAJA tidak diikutkan di sini
    ]);

    if ($emailChanged) {
        $this->validate([
            'email' => ['required', 'email:rfc,dns', 'unique:users,email', 'unique:users,pending_email'],
        ]);

        $user->update(['pending_email' => $this->email]);
        $this->sendPendingEmailConfirmation($user);

        $this->alert('info', "Link konfirmasi dikirim ke {$this->email}. Email akun kamu belum berubah sampai link itu diklik.", [
            'toast' => false, 'position' => 'center',
        ]);

        $this->email = $user->email; // kembalikan tampilan form ke email LAMA (yang masih aktif), bukan yang baru diketik
    } else {
        $this->alert('success', 'Profil berhasil diperbarui.', ['toast' => true, 'position' => 'top-end']);
    }
}

protected function sendPendingEmailConfirmation($user)
{
    $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'profile.confirm-email',
        now()->addMinutes(60),
        ['user' => $user->id, 'hash' => sha1($user->pending_email)]
    );

    \Illuminate\Support\Facades\Mail::to($user->pending_email)->send(new \App\Mail\ConfirmNewEmailMail($user, $url));
}
```

#### Route & controller konfirmasi

```php
Route::get('/profile/confirm-email/{user}/{hash}', \App\Http\Controllers\ConfirmPendingEmailController::class)
    ->middleware(['auth', 'signed'])
    ->name('profile.confirm-email');
```

```php
class ConfirmPendingEmailController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        abort_unless($user->id === auth()->id(), 403);
        abort_if(! $user->pending_email, 404, 'Tidak ada perubahan email yang menunggu konfirmasi.');
        abort_unless(hash_equals(sha1($user->pending_email), (string) $request->route('hash')), 403);

        $user->forceFill([
            'email' => $user->pending_email,
            'pending_email' => null,
            'email_verified_at' => now(), // langsung terverifikasi, karena baru saja terbukti kepemilikannya
        ])->save();

        return redirect()->route('email.verified'); // reuse halaman sukses dari section 1G
    }
}
```

#### UI di halaman Profil — tampilkan status pending kalau ada

```html
@if(auth()->user()->pending_email)
<div
    class="border border-steel/40 bg-steel/5 rounded-sm p-3 mb-4 text-sm text-ink flex items-center justify-between"
>
    <span
        >Menunggu konfirmasi ke
        <strong>{{ auth()->user()->pending_email }}</strong></span
    >
    <div class="flex gap-3">
        <button
            wire:click="cancelPendingEmail"
            class="text-ink-muted underline text-xs"
        >
            Batalkan
        </button>
        <button
            wire:click="resendPendingEmail"
            wire:loading.attr="disabled"
            class="text-steel underline text-xs"
        >
            Kirim Ulang
        </button>
    </div>
</div>
@endif
```

```php
public function cancelPendingEmail()
{
    auth()->user()->update(['pending_email' => null]);
    $this->alert('info', 'Perubahan email dibatalkan.', ['toast' => true, 'position' => 'top-end']);
}

public function resendPendingEmail()
{
    $this->sendPendingEmailConfirmation(auth()->user());
    $this->dispatch('cooldown-start', seconds: 60); // reuse pola cooldown dari section 1F
}
```

**Kenapa ini lebih aman:**

- User nggak bisa "ngunci" akunnya sendiri dengan ngetik email asal — email lama tetap valid dan tetap bisa dipakai login sampai email baru terbukti kepemilikannya.
- Tidak ada jeda waktu di mana akun berstatus "unverified" gara-gara ganti email — jadi middleware `verified` di section 1C tidak akan pernah ke-trigger oleh aksi ganti email, cuma relevan untuk user yang baru daftar dan belum pernah verifikasi sama sekali.
- Kalau user salah ketik email baru (typo), dia nggak pernah kehilangan akses — link konfirmasi cuma nyampe kalau emailnya beneran valid & dia yang punya.

---

## 1E. Bonus — Validasi Email Async/Real-time di Form Register

> Ini pelengkap UX, BUKAN pengganti verifikasi email di section 1. Async check di sini cuma bisa mastiin format valid + domain punya mail server (DNS) + belum terdaftar — bukan mastiin mailbox itu beneran aktif dipakai orang. Kepastian "email aktif" tetap dari klik link verifikasi.

### Logic di komponen `Register.php`

Pakai `wire:model.blur` (validasi jalan pas user pindah dari field email, BUKAN tiap ketikan huruf — supaya nggak nge-hit DNS lookup berkali-kali pas user masih ngetik):

```php
public string $email = '';
public bool $emailChecking = false;
public bool $emailValid = false;

public function updatedEmail($value)
{
    $this->emailValid = false;
    $this->resetErrorBag('email');

    if (empty($value)) return;

    $this->validateOnly('email', [
        'email' => 'required|email:rfc,dns|unique:users,email',
    ], [
        'email.email' => 'Format email tidak valid atau domain tidak ditemukan.',
        'email.unique' => 'Email ini sudah terdaftar. Sudah punya akun?',
    ]);

    $this->emailValid = ! $this->getErrorBag()->has('email');
}
```

### UI — indikator status di dalam field

```html
<label class="text-xs font-mono uppercase text-ink-muted tracking-wide"
    >Email</label
>
<div class="relative">
    <input
        type="email"
        wire:model.blur="email"
        class="w-full border rounded-sm px-4 py-2.5 text-sm mt-1 focus:outline-none focus:ring-1
            {{ $errors->has('email') ? 'border-red-300' : ($emailValid ? 'border-green-300' : 'border-hairline focus:border-amber focus:ring-amber') }}"
    />

    <span
        wire:loading
        wire:target="email"
        class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-muted text-xs font-mono"
    >
        Memeriksa...
    </span>
    @if($emailValid)
    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-green-600"
        >✓</span
    >
    @endif
</div>
@error('email')
<p class="text-red-500 text-xs mt-1 mb-4">{{ $message }}</p>
@else
<div class="mb-4"></div>
@enderror
```

### Tombol "Daftar" tetap validasi ulang di server saat submit

Async check ini cuma bantu UX di awal — validasi lengkap (termasuk semua rule password) tetap harus jalan lagi saat tombol "Daftar" diklik, jangan cuma andalkan hasil check per-field tadi (race condition kecil selalu mungkin, misal email keburu didaftarkan orang lain tepat di antara blur-check dan submit).

---

## 1F. Polish — Tombol "Kirim Ulang" Butuh Loading State + Disabled + Countdown 1 Menit

Pola ini SAMA PERSIS dibutuhkan di 2 tempat: tombol "Kirim Ulang Email Verifikasi" (halaman Verify Email Notice) dan tombol "Kirim Link Reset" (halaman Forgot Password). Buat sekali, pakai di dua-duanya — jangan ditulis dua kali beda-beda.

### Server side (Livewire) — dispatch event saat berhasil kirim

```php
public function sendResetLink() // atau resendVerification() untuk halaman verify
{
    $this->validate(['email' => 'required|email']);
    $status = Password::sendResetLink(['email' => $this->email]);

    if ($status === Password::RESET_LINK_SENT) {
        $this->dispatch('cooldown-start', seconds: 60); // trigger countdown di client
        $this->alert('success', 'Link reset sudah dikirim.', ['toast' => true, 'position' => 'top-end']);
    } else {
        $this->addError('email', 'Email tidak ditemukan.');
    }
}
```

### Client side (Alpine) — loading, disabled, dan countdown

```html
<div
    x-data="{ cooldown: 0 }"
    x-on:cooldown-start.window="
        cooldown = $event.detail.seconds;
        let t = setInterval(() => { cooldown--; if (cooldown <= 0) clearInterval(t); }, 1000);
     "
>
    <button
        wire:click="sendResetLink"
        wire:loading.attr="disabled"
        wire:target="sendResetLink"
        :disabled="cooldown > 0"
        class="w-full bg-amber text-ink font-medium py-2.5 rounded-sm disabled:opacity-50 disabled:cursor-not-allowed transition-opacity"
    >
        <span wire:loading wire:target="sendResetLink">Mengirim...</span>
        <span
            wire:loading.remove
            wire:target="sendResetLink"
            x-show="cooldown === 0"
            >Kirim Link Reset</span
        >
        <span
            wire:loading.remove
            wire:target="sendResetLink"
            x-show="cooldown > 0"
            x-text="'Terkirim — Kirim Ulang (' + cooldown + 's)'"
        ></span>
    </button>
</div>
```

Behavior yang dihasilkan:

1. Klik tombol → langsung disabled + tulisan "Mengirim..." (loading state kelihatan, sesuai yang diminta)
2. Berhasil terkirim → tulisan berubah jadi "Terkirim — Kirim Ulang (60s)", tombol tetap disabled
3. Countdown jalan turun tiap detik di client (tidak perlu round-trip ke server tiap detik)
4. Begitu sampai 0 detik → tombol otomatis aktif lagi, tulisan balik ke semula, siap diklik ulang

**Terapkan pola ini di 2 file:** `Auth/VerifyEmailNotice.php` (+ view-nya) dan `Auth/ForgotPassword.php` (+ view-nya). Kalau mau lebih rapi, boleh diekstrak jadi satu Blade component reusable (`<x-cooldown-button wire:click="..." seconds="60">Kirim Link Reset</x-cooldown-button>`) supaya nggak nulis Alpine yang sama dua kali — opsional, tapi lebih maintainable.

---

## 1G. Halaman "Verifikasi Berhasil" — Jangan Langsung Redirect ke Dashboard

**Sekarang:** klik "Verify Email Address" di email → langsung nyemplung ke `/dashboard` tanpa jeda.
**Yang diminta:** mampir dulu ke halaman konfirmasi "Verifikasi Berhasil", ada tombol buat lanjut manual, DAN auto-redirect otomatis kalau didiamkan 5 detik.

### Route baru

```php
Route::get('/email/verified', function () {
    return view('auth.email-verified');
})->middleware('auth')->name('email.verified');
```

### Ubah tujuan redirect di controller verifikasi

Cari controller yang menangani klik link verifikasi (`VerifyEmailController` atau setara), ganti redirect tujuannya:

```php
public function __invoke(EmailVerificationRequest $request)
{
    if (! $request->user()->hasVerifiedEmail()) {
        $request->user()->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Verified($request->user()));
    }

    return redirect()->route('email.verified'); // BUKAN langsung ke dashboard
}
```

### Halaman `auth/email-verified.blade.php`

Reuse layout split-panel yang sama seperti Login/Register biar konsisten:

```html
<div
    class="text-center max-w-sm mx-auto"
    x-data="{ seconds: 5 }"
    x-init="
        const t = setInterval(() => {
            seconds--;
            if (seconds <= 0) { clearInterval(t); window.location.href = '{{ route('dashboard') }}'; }
        }, 1000);
     "
>
    <div class="text-green-600 text-4xl mb-4">✓</div>
    <h1 class="font-display font-bold text-2xl text-ink mb-2">
        Verifikasi Berhasil!
    </h1>
    <p class="text-ink-muted text-sm mb-6">
        Email kamu sudah terverifikasi. Diarahkan otomatis dalam
        <span x-text="seconds" class="font-mono font-medium text-ink"></span>
        detik.
    </p>
    <a
        href="{{ route('dashboard') }}"
        class="bg-amber text-ink font-medium px-6 py-2.5 rounded-sm inline-block"
    >
        Ke Dashboard Sekarang
    </a>
</div>
```

> Catatan: kalau user klik link verifikasi dari device/browser yang BEDA (belum login di situ), route ini tetap kena middleware `auth` dulu — dia bakal diarahkan login dulu, baru habis itu otomatis lanjut ke halaman ini. Itu perilaku normal, tidak perlu diubah.

---

## 1H. 🐛 Bug yang Ditemukan — SUDAH TIDAK RELEVAN, Digantikan oleh Section 1D (Revisi)

> Bug ini ditemukan saat testing manual (ganti email → verifikasi terkirim tapi belum diklik → tetap bisa akses dashboard). Setelah desain ganti-email direvisi total di **section 1D** (pola "pending email", bukan update langsung), skenario bug ini **tidak akan terjadi lagi** — karena `users.email` sekarang tidak pernah berubah sebelum email baru dikonfirmasi, jadi tidak akan pernah ada momen "akun jadi unverified karena ganti email".
>
> **Kerjakan section 1D (revisi) saja** — bagian di bawah ini dibiarkan sebagai catatan sejarah/konteks kenapa desainnya diubah, TIDAK perlu diimplementasikan lagi.

<details>
<summary>Catatan lama (sudah tidak berlaku, klik untuk lihat konteks)</summary>

**Gejala dari testing manual:** ganti email di halaman Profil → email verifikasi terkirim (bagian ini jalan) → TAPI tidak ada feedback apapun di halaman Profil itu sendiri → lalu logout & login lagi, ternyata bisa langsung masuk dashboard tanpa diminta verifikasi ulang.

⚠️ Login tetap berhasil itu WAJAR (autentikasi ≠ verifikasi) — yang jadi masalah adalah `users.email` sudah berubah duluan sebelum terbukti valid. Ini akar masalah yang diselesaikan oleh desain baru di section 1D, bukan dengan menambal middleware.

</details>

---

Sama seperti email verification, Laravel sudah punya sistem ini bawaan (`password_reset_tokens` table biasanya sudah ada dari migration default) — tinggal dibuatkan UI Livewire-nya.

### A. Halaman Lupa Password

```php
Route::get('/forgot-password', \App\Livewire\Auth\ForgotPassword::class)
    ->middleware('guest')->name('password.request');
```

```php
use Illuminate\Support\Facades\Password;

public function sendResetLink()
{
    $this->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(['email' => $this->email]);

    if ($status === Password::RESET_LINK_SENT) {
        $this->alert('success', 'Link reset password sudah dikirim ke email kamu.'); // pakai LivewireAlert yang sudah terpasang
    } else {
        $this->addError('email', 'Email tidak ditemukan.');
    }
}
```

### B. Halaman Reset Password

```php
Route::get('/reset-password/{token}', \App\Livewire\Auth\ResetPassword::class)
    ->middleware('guest')->name('password.reset');
```

```php
public function resetPassword()
{
    $this->validate([
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        ['email' => $this->email, 'password' => $this->password, 'password_confirmation' => $this->password_confirmation, 'token' => $this->token],
        function ($user) {
            $user->forceFill(['password' => bcrypt($this->password)])->save();
        }
    );

    if ($status === Password::PASSWORD_RESET) {
        return redirect()->route('login')->with('success', 'Password berhasil direset, silakan login.');
    }

    $this->addError('email', 'Link reset tidak valid atau sudah kedaluwarsa.');
}
```

### C. UI

Kedua halaman ini reuse layout split-panel yang sama seperti Login/Register (`FRONTEND_CHECKOUT_POLISH.md` section 2). Tambahkan link "Lupa password?" di halaman Login, di bawah field password.

### D. Rate limit

```php
Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    // forgot-password & reset-password routes
});
```

---

## 3. Single Session Login — Satu Akun Satu Sesi Aktif (Poin 4)

Ini yang paling teknis. Prinsipnya: setiap kali user login, sistem generate token unik baru, simpan di DB user DAN di sesi browser itu. Sesi lain (kalau ada) otomatis kedeteksi "beda token" di request berikutnya dan langsung dilempar logout.

### A. Kolom baru

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('current_session_token')->nullable();
});
```

### B. Update saat login berhasil

Di `Auth/Login.php`, setelah `auth()->attempt(...)` sukses:

```php
$token = \Illuminate\Support\Str::random(60);
auth()->user()->update(['current_session_token' => $token]);
session(['session_token' => $token]);
```

### C. Middleware `EnsureSingleSession`

```bash
php artisan make:middleware EnsureSingleSession
```

```php
public function handle($request, Closure $next)
{
    if (auth()->check()) {
        $sessionToken = session('session_token');
        $userToken = auth()->user()->current_session_token;

        if ($sessionToken !== $userToken) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Akun ini baru saja login dari perangkat lain.');
        }
    }
    return $next($request);
}
```

Daftarkan di group `auth` yang sama persis dengan `EnsureUserIsNotBanned` (dari `ADMIN_PANEL_SPEC.md`) — jadi satu request cuma perlu lewat middleware ini sekali, dicek bareng.

### D. Bonus insight — "remember me" otomatis ikut aman

Laravel secara bawaan meng-generate `remember_token` baru setiap kali login dengan "Remember Me" dicentang, dan otomatis membatalkan `remember_token` lama. Jadi kombinasi mekanisme ini + `current_session_token` di atas menutup DUA jalur (sesi biasa & sesi "remember me") sekaligus — tidak perlu kerjaan tambahan untuk itu.

### E. Konsekuensi UX yang perlu diketahui

Dengan fitur ini aktif, **kamu sendiri** kalau login dari 2 device buat testing akan saling nge-logout satu sama lain — itu memang perilaku yang diminta, bukan bug. Kalau nanti butuh testing paralel (misal testing sebagai 2 user berbeda), pakai 2 akun berbeda atau browser profile terpisah, bukan akun yang sama di 2 tempat.

---

## 4. Tambahan yang Saya Sarankan (di luar 4 poin awal)

### A. Pesan ban langsung di form login, bukan nunggu di-bounce

Sekarang, user yang di-ban tetap berhasil "login" dulu (credential match), baru ke-logout paksa oleh middleware di halaman berikutnya. Lebih baik dicek langsung di `login()`:

```php
if (auth()->attempt([...])) {
    if (auth()->user()->banned_at) {
        auth()->logout();
        $this->addError('email', 'Akun ini telah dinonaktifkan.');
        return;
    }
    // lanjut proses login normal + set session_token
}
```

### B. Re-verifikasi email saat ganti email di Profile

Sudah dijelaskan di section 1D.

### C. Rate limit halaman forgot-password

Sudah dijelaskan di section 2D — jangan sampai fitur ini disalahgunakan buat spam email ke orang lain.

---

## 5. Definition of Done

- [ ] Setelah register, email verifikasi masuk ke inbox (cek beneran, jangan asumsi)
- [ ] User yang belum verifikasi email tidak bisa akses dashboard/tools, diarahkan ke halaman "cek email kamu"
- [ ] Tombol "Kirim Ulang Email" di halaman verifikasi beneran mengirim ulang
- [ ] Ganti email di Profile TIDAK langsung mengubah `users.email` — tersimpan dulu sebagai `pending_email`, baru berpindah setelah link konfirmasi diklik
- [ ] Selama ada `pending_email` menggantung, email LAMA tetap bisa dipakai login normal tanpa gangguan
- [ ] Tombol "Batalkan" pada perubahan email yang pending berfungsi (mengosongkan `pending_email`)
- [ ] Tombol "Kirim Ulang" konfirmasi email pending pakai pola cooldown yang sama seperti section 1F
- [ ] Lupa Password: email reset masuk ke inbox, link berfungsi, password baru bisa dipakai login
- [ ] Link reset password yang sudah dipakai/kedaluwarsa tidak bisa dipakai ulang
- [ ] Login di Device A, lalu login akun SAMA di Device B → Device A otomatis ter-logout di request berikutnya dengan pesan yang jelas
- [ ] User yang di-ban mendapat pesan error LANGSUNG di form login, tidak perlu nunggu redirect dulu
- [ ] Semua halaman baru (verify notice, forgot password, reset password) konsisten dengan design system yang sudah ada
- [ ] Field email di form Register menampilkan status (memeriksa/valid/error) setelah user pindah dari field itu, tanpa perlu submit form dulu
- [ ] Validasi email tetap dijalankan ulang di server saat submit "Daftar", tidak cuma mengandalkan hasil check async
