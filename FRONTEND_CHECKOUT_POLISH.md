# 🎨 ADDENDUM SPEC — Halaman Publik, Checkout UX, & Pending Transaction

> Lanjutan dari `DESIGN_RESTYLE_BRIEF.md` (yang fokusnya baru dashboard) dan `SUBSCRIPTION_SPEC.md`. Dokumen ini menyasar 3 hal: (1) halaman publik/auth yang belum ikut restyle, (2) ganti checkout Midtrans dari popup ke embed, (3) cegah duplikasi pending transaction.

---

## 1. Landing Page Publik (`/` untuk guest)

**Asumsi kerja:** karena sekarang sudah ada model bisnis (Free vs Pro), root `/` untuk guest sebaiknya BUKAN langsung redirect ke `/login` lagi, tapi jadi halaman landing singkat yang menjelaskan produk sebelum orang daftar — biar ada konteks kenapa harus daftar. Kalau ternyata kamu maunya tetap langsung ke `/login` tanpa landing page, kasih tahu saya, gampang diubah balik.

**Isi halaman (satu halaman panjang, bukan banyak section rumit):**

1. Header simpel: logo kiri, tombol "Masuk" & "Daftar Gratis" kanan (pakai style dari section 2 di bawah — hairline border, bukan gradient)
2. Hero: headline singkat gaya utilitarian (contoh: "Compress, convert, done.") + subheading 1 kalimat + tombol CTA "Daftar Gratis" (amber). Boleh reuse motif anotasi before/after (ukuran file dicoret → ukuran kecil) sebagai elemen visual khas, sesuai konsep Blueprint yang sudah ada.
3. Section "Kumpulan Tools" — reuse `config/tools.php`, tampilkan sebagai preview singkat (nama + deskripsi tools), TANPA perlu login untuk sekadar melihat daftar tools apa saja yang tersedia.
4. Section Pricing singkat — versi ringkas dari halaman `/pricing` (Free vs Pro, harga, 1 tombol "Lihat Detail" ke `/pricing`).
5. Footer simpel, monospace kecil.

---

## 2. Login & Register — Redesign

**Sekarang:** kemungkinan masih pakai styling default Laravel/Livewire (belum ikut sistem desain Blueprint). Ganti jadi layout split-panel: kiri panel brand gelap, kanan form.

```html
<div class="min-h-screen flex">
    <!-- Panel kiri: brand, disembunyikan di mobile -->
    <div
        class="hidden lg:flex w-1/2 bg-ink text-white flex-col justify-between p-12"
    >
        <span class="font-display font-bold text-xl">ToolBox</span>
        <div>
            <p class="font-display text-3xl font-bold mb-3 leading-tight">
                Compress, convert, done.
            </p>
            <p class="text-slate-400 text-sm">
                Kumpulan tools file yang cepat dan rapi.
            </p>
        </div>
        <p class="font-mono text-xs text-slate-500">
            © {{ date('Y') }} ToolBox
        </p>
    </div>

    <!-- Panel kanan: form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center bg-paper p-8">
        <div class="w-full max-w-sm">
            <h1 class="font-display font-bold text-2xl text-ink mb-1">Masuk</h1>
            <p class="text-ink-muted text-sm mb-6">Selamat datang kembali</p>

            <label
                class="text-xs font-mono uppercase text-ink-muted tracking-wide"
                >Email</label
            >
            <input
                type="email"
                class="w-full border border-hairline rounded-sm px-4 py-2.5 text-sm mb-4 mt-1 focus:outline-none focus:border-amber focus:ring-1 focus:ring-amber"
            />

            <label
                class="text-xs font-mono uppercase text-ink-muted tracking-wide"
                >Password</label
            >
            <input
                type="password"
                class="w-full border border-hairline rounded-sm px-4 py-2.5 text-sm mb-6 mt-1 focus:outline-none focus:border-amber focus:ring-1 focus:ring-amber"
            />

            <button
                class="w-full bg-amber text-ink font-medium py-2.5 rounded-sm"
            >
                Masuk
            </button>
            <p class="text-center text-sm text-ink-muted mt-4">
                Belum punya akun?
                <a href="/register" class="text-steel font-medium">Daftar</a>
            </p>
        </div>
    </div>
</div>
```

Halaman Register pakai layout & style identik, cuma field-nya beda (name, email, password, konfirmasi password). Panel kiri boleh sama persis atau tagline sedikit beda.

---

## 3. Halaman Pricing & Billing — Restyle

Terapkan token desain yang sama seperti dashboard (`border-hairline`, `rounded-sm`, `font-mono` untuk angka harga, `bg-ink` untuk elemen highlight).

- Tabel perbandingan Free vs Pro: styling seperti spec-sheet (border hairline antar baris, bukan card terpisah-pisah dengan shadow).
- Plan yang lagi aktif (misal user sudah Pro) dapat penanda border kiri amber, sama persis pola "active state" yang dipakai di sidebar — ini detail kecil yang bikin konsisten di seluruh sistem.
- Harga pakai `font-mono font-bold text-3xl`, contoh: `Rp 49.000` / `<span class="text-sm text-ink-muted font-sans">per 30 hari</span>`.

---

## 4. Checkout — Snap Embed, Bukan Popup

**Ganti dari:**

```js
snap.pay(snapToken, { onSuccess, onPending, onError });
```

**Jadi:**

```html
<!-- di halaman /pricing atau /checkout, setelah user klik Upgrade -->
<div
    id="snap-container"
    class="border border-hairline rounded-sm bg-white p-4 min-h-[480px]"
></div>

<script>
    snap.embed("{{ $snapToken }}", {
        embedId: "snap-container",
        onSuccess: function (result) {
            window.location.href = "/billing?status=success";
        },
        onPending: function (result) {
            window.location.href = "/billing?status=pending";
        },
        onError: function (result) {
            // tampilkan pesan error dengan style kita sendiri, jangan pakai alert() bawaan browser
            document
                .getElementById("checkout-error")
                .classList.remove("hidden");
        },
    });
</script>
```

Catatan: isi form pemilihan metode pembayaran DI DALAM container itu tetap tampilan bawaan Midtrans (nggak bisa di-custom CSS sepenuhnya) — tapi minimal sudah tidak lagi jadi modal popup lepas dari halaman. Cek juga di Dashboard Midtrans (Settings → Snap Preferences) — biasanya ada opsi ganti warna utama/logo Snap, sesuaikan ke warna amber kita kalau tersedia.

> Kalau suatu saat mau kontrol desain 100% penuh (termasuk bentuk pilihan metode bayarnya), itu perlu migrasi ke **Core API** Midtrans (bukan Snap) — dicatat sebagai opsi lanjutan, TIDAK perlu dikerjakan sekarang.

---

## 4b. Perbaikan Halaman Checkout ("Selesaikan Pembayaran")

**Ditemukan dari screenshot:** halaman ini masih pakai header versi LAMA (logo ungu), berarti halaman ini punya layout/header sendiri yang terpisah dari layout dashboard yang sudah di-restyle. Selain itu ruang di sebelah kanan widget Snap dibiarkan kosong total, bikin halaman terasa belum jadi.

**Fix wajib #1 — root cause, bukan cuma tempel warna:**
Pastikan halaman checkout ini me-render header dari **komponen/partial yang sama persis** dengan yang dipakai di dashboard (`layouts/dashboard.blade.php` atau komponen header terpisah kalau sudah di-extract). JANGAN ada markup header kedua yang di-copy-paste manual di halaman checkout — itu penyebab kenapa halaman ini "ketinggalan" waktu header lain diupdate. Kalau sekarang memang ada 2 versi header di kode, gabungkan jadi satu komponen (`<x-app-header />` misalnya), dipakai di semua halaman termasuk checkout.

**Fix #2 — isi ruang kosong dengan panel ringkasan pesanan, bukan dibiarkan kosong:**

```html
<div class="max-w-5xl mx-auto px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="font-display font-bold text-2xl text-ink">
            Selesaikan Pembayaran
        </h1>
        <a
            href="{{ route('pricing') }}"
            class="font-mono text-xs uppercase text-ink-muted underline hover:text-ink"
        >
            Batal / Ganti Paket
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Kiri: widget Snap Embed (biarkan apa adanya, itu bawaan Midtrans) -->
        <div
            id="snap-container"
            class="border border-hairline rounded-sm bg-white overflow-hidden min-h-[500px]"
        ></div>

        <!-- Kanan: ringkasan pesanan bergaya kita sendiri, isi ruang kosong -->
        <div class="border border-hairline rounded-sm bg-white p-6">
            <p
                class="font-mono text-xs uppercase tracking-widest text-ink-muted mb-4"
            >
                Ringkasan Pesanan
            </p>

            <div
                class="flex justify-between items-center pb-4 border-b border-hairline mb-4"
            >
                <span class="font-display font-bold text-lg text-ink"
                    >Paket Pro</span
                >
                <span class="font-mono font-bold text-2xl text-ink"
                    >Rp49.000</span
                >
            </div>

            <ul class="space-y-2 text-sm text-ink-muted mb-6">
                <li class="flex gap-2">
                    <span class="text-amber">✓</span> Semua tools unlimited
                </li>
                <li class="flex gap-2">
                    <span class="text-amber">✓</span> Semua preset & fitur
                    terbuka
                </li>
                <li class="flex gap-2">
                    <span class="text-amber">✓</span> Ukuran file maksimal lebih
                    besar
                </li>
                <li class="flex gap-2">
                    <span class="text-amber">✓</span> Aktif 30 hari
                </li>
            </ul>

            <p class="font-mono text-xs text-ink-muted">
                Order ID:
                <span class="text-ink"
                    >#{{ $subscription->midtrans_order_id }}</span
                >
            </p>
        </div>
    </div>
</div>
```

Panel kanan ini isinya statis (dari data plan & subscription yang sudah ada di server), bukan bagian dari widget Midtrans — jadi bebas kita desain sesuai sistem desain sendiri (hairline border, font mono untuk harga, checklist amber). Ini juga sekalian jadi tempat reminder ke user "kamu bayar buat apa" selagi dia mikir metode pembayaran, biar nggak batal di tengah jalan.

---

## 5. Cegah Duplikasi Pending Transaction

**Masalah sekarang:** kalau user klik "Upgrade" lagi padahal masih ada transaksi pending sebelumnya, sistem kemungkinan bikin subscription/order baru — jadi ada beberapa row `pending` menumpuk, sekaligus bikin bingung transaksi mana yang harus diselesaikan.

### Tambahan kolom di tabel `subscriptions`

```php
$table->text('snap_token')->nullable();
```

### Logic sebelum bikin transaksi baru

```php
public function initiateCheckout(User $user)
{
    $pending = $user->subscriptions()
        ->where('status', 'pending')
        ->where('created_at', '>', now()->subHours(24)) // asumsi masa berlaku transaksi Midtrans ~24 jam
        ->latest()
        ->first();

    if ($pending && $pending->snap_token) {
        // JANGAN bikin transaksi baru — user harus selesaikan yang lama dulu
        return $pending->snap_token;
    }

    if ($pending) {
        // pending lama sudah lewat masa berlaku, anggap expired
        $pending->update(['status' => 'expired']);
    }

    // baru bikin transaksi baru seperti biasa (lihat SUBSCRIPTION_SPEC.md section 5)
    // ...simpan snap_token ke kolom baru saat create Subscription
}
```

### UI-nya

Kalau user masih punya pending, tombol "Upgrade" di halaman Pricing berubah jadi "Selesaikan Pembayaran" (bukan hilang/disable total), dan mengarah ke checkout yang sama (pakai `snap_token` yang tersimpan), bukan bikin baru.

### Reconciliation — jaring pengaman kalau webhook telat/gagal

Selain nunggu webhook, tambahkan pengecekan aktif saat user membuka halaman Billing dan statusnya masih `pending`:

```php
if ($subscription->status === 'pending') {
    $status = \Midtrans\Transaction::status($subscription->midtrans_order_id);
    if (in_array($status->transaction_status, ['settlement', 'capture']) && ($status->fraud_status ?? 'accept') === 'accept') {
        // webhook mungkin belum/gagal sampai — update manual dari hasil cek status API
        $subscription->update([
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addDays(config('plans.pro.duration_days')),
        ]);
    }
}
```

Ini jaring pengaman — webhook Midtrans umumnya reliable tapi tidak 100% dijamin sampai (bisa gagal karena server kita sempat down, dsb).

---

## 6. Fix: Tombol "Perpanjang Langganan" Cuma Muncul Kalau Relevan

**Revisi dari desain sebelumnya:** bukan cuma soal tombolnya nyasar ke halaman yang salah — tombolnya memang **tidak seharusnya selalu tampil** selama user masih Pro aktif dengan sisa waktu banyak. Percuma juga ditampilkan terus kalau user belum butuh perpanjang.

### Logic tampilan di halaman Billing, berdasarkan sisa hari

```php
$subscription = auth()->user()->activeSubscription();
$daysRemaining = $subscription ? now()->diffInDays($subscription->expires_at, false) : null;
```

| Kondisi                                                      | Tampilan                                                                                          |
| ------------------------------------------------------------ | ------------------------------------------------------------------------------------------------- |
| Tidak ada subscription aktif (Free / belum pernah langganan) | Card "Kamu masih Free" + tombol "Upgrade ke Pro" → boleh tetap ke `/pricing` seperti biasa        |
| Pro aktif, `daysRemaining > 7`                               | Cuma info: "Pro · Aktif · Berlaku sampai [tanggal]" — **TIDAK ada tombol Perpanjang sama sekali** |
| Pro aktif, `daysRemaining <= 7` (tapi masih aktif)           | Muncul banner peringatan (amber) + tombol **"Perpanjang Sekarang"**                               |
| Pro sudah lewat (`expires_at` sudah lewat)                   | Banner "Langganan kamu sudah berakhir" + tombol **"Perpanjang Langganan"** menonjol               |

### Contoh markup

```html
@if(!$subscription)
<p class="text-ink-muted text-sm">Kamu masih pakai paket Free.</p>
<a href="{{ route('pricing') }}" class="...">Upgrade ke Pro</a>

@elseif($daysRemaining > 7)
<p class="text-sm text-ink-muted">
    Berlaku sampai
    <span class="text-ink font-medium"
        >{{ $subscription->expires_at->translatedFormat('d F Y, H:i') }}</span
    >
</p>
{{-- tidak ada tombol apapun di sini --}} @elseif($daysRemaining >= 0)
<div
    class="border border-amber/40 bg-amber/5 rounded-sm p-4 flex items-center justify-between"
>
    <p class="text-sm text-ink">
        Langganan Pro kamu berakhir dalam
        <span class="font-mono font-medium">{{ $daysRemaining }} hari</span>
        ({{ $subscription->expires_at->translatedFormat('d F Y') }})
    </p>
    <a
        href="{{ route('checkout.renew') }}"
        class="bg-amber text-ink font-medium px-4 py-2 rounded-sm text-sm whitespace-nowrap"
    >
        Perpanjang Sekarang
    </a>
</div>

@else
<div
    class="border border-red-300 bg-red-50 rounded-sm p-4 flex items-center justify-between"
>
    <p class="text-sm text-ink">Langganan Pro kamu sudah berakhir.</p>
    <a
        href="{{ route('checkout.renew') }}"
        class="bg-amber text-ink font-medium px-4 py-2 rounded-sm text-sm whitespace-nowrap"
    >
        Perpanjang Langganan
    </a>
</div>
@endif
```

### Routing tombol tetap langsung ke checkout (bukan lewat pricing)

Kapanpun tombol "Perpanjang" ini muncul (baik di kondisi ≤7 hari maupun sudah expired), tetap arahkan LANGSUNG ke checkout, skip halaman pricing — alasannya sama seperti sebelumnya: user di titik ini sudah jelas mau bayar, nggak perlu ditanya lagi "mau pilih paket apa".

```php
Route::get('/checkout/renew', [CheckoutController::class, 'renew'])
    ->middleware('auth')
    ->name('checkout.renew');
```

```php
public function renew()
{
    $snapToken = $this->initiateCheckout(auth()->user()); // logic dari section 5, termasuk cek pending existing
    return view('checkout', compact('snapToken'));
}
```

### Bonus (opsional, tidak wajib sekarang)

Kirim notifikasi (in-app banner cukup, email opsional) otomatis sekali saat subscription pertama kali masuk window "≤7 hari lagi" — bisa pakai scheduled command harian, pola yang sama seperti `CleanupTempFiles`, cek subscription yang `expires_at` antara hari ini dan +7 hari yang belum pernah dikirimi reminder, tandai `reminder_sent_at` biar nggak dikirim berkali-kali tiap hari.

---

## 8. Fix: Transaksi Pending Lama Jadi Nyangkut & Riwayat Bisa Diklik Bayar Lagi

**Dua masalah terpisah yang keliatan dari kasus ini:**

1. Ada transaksi lain yang sudah aktif (menggantikan), tapi transaksi pending yang lebih lama tetap nyangkut berstatus "Pending" selamanya di riwayat.
2. Baris di tabel Riwayat Transaksi bisa diklik dan mengarahkan ke halaman pricing/checkout — padahal riwayat harusnya cuma catatan, bukan pintu bayar.

### Fix A — Auto-tutup pending lain begitu ada yang jadi aktif

Tambahkan di webhook handler, tepat setelah subscription baru berhasil di-set `active`:

```php
// setelah $subscription->update(['status' => 'active', ...]);

\App\Models\Subscription::where('user_id', $subscription->user_id)
    ->where('id', '!=', $subscription->id)
    ->where('status', 'pending')
    ->update(['status' => 'expired']);
```

Ini juga sekalian menutup celah yang sama untuk kasus 3 transaksi ditest berturut-turut seperti tadi — begitu ada yang aktif, sisanya otomatis rapi jadi "Expired", bukan nyangkut "Pending".

### Fix B — Riwayat Transaksi jadi read-only, bukan link

Baris di tabel Riwayat Transaksi TIDAK boleh mengarahkan ke halaman pricing/checkout apapun statusnya. Kalau mau tetap ada interaksi, cukup buka detail kecil (bisa modal/expand row), isinya cuma info (Order ID lengkap, tanggal, nominal, status) — bukan tombol bayar.

```html
<!-- baris riwayat: hilangkan <a href> atau wire:click yang redirect ke pricing/checkout -->
<tr class="hover:bg-paper/50">
    <!-- cukup hover state biasa, bukan cursor-pointer + navigasi -->
    <td>...</td>
</tr>
```

Kalau memang user butuh menyelesaikan/memperpanjang pembayaran, itu SUDAH ditangani oleh banner status di bagian atas halaman Billing (section 6) — bukan dari klik baris riwayat. Dua jalur ini sengaja dipisah: **card atas** = aksi yang relevan sekarang, **tabel riwayat** = arsip read-only.

---

---

## 10. Fix: Pending yang Ditinggal Harus Bisa Dilanjutkan, Bukan Bikin Baru Terus

**Bug ditemukan langsung dari testing:** user klik "Perpanjang Sekarang" → masuk checkout → keluar tanpa bayar → transaksi jadi "Pending" di riwayat. Klik "Perpanjang Sekarang" lagi harusnya **melanjutkan** transaksi pending yang sama, tapi yang kejadian malah transaksi pending lama langsung di-expire dan dibikin transaksi baru. Ini kebalikan dari yang diminta di section 5 — cek ulang urutan logic-nya, kemungkinan besar bagian "reuse kalau masih ada pending valid" ke-skip atau salah taruh setelah bagian "expire yang lama".

### Fix logic `initiateCheckout()` — urutan pengecekan harus begini, jangan dibalik

```php
public function initiateCheckout(User $user)
{
    $pending = $user->subscriptions()
        ->where('status', 'pending')
        ->where('created_at', '>', now()->subHours(24))
        ->latest()
        ->first();

    // 1. Kalau ada pending yang MASIH VALID (belum 24 jam) dan punya snap_token → PAKAI ITU LAGI, STOP DI SINI
    if ($pending && $pending->snap_token) {
        return $pending->snap_token;
    }

    // 2. Baru kalau pending itu sudah lewat 24 jam, DI SINI baru boleh di-expire
    if ($pending) {
        $pending->update(['status' => 'expired']);
    }

    // 3. Baru bikin transaksi baru kalau memang tidak ada pending valid sama sekali
    // ...generate order baru seperti biasa (section 5)
}
```

Poin (1) harus dicek dan STOP sebelum sampai ke poin (2) — kalau kodenya sekarang selalu jalan ke poin (2)/(3) duluan, itu penyebab bug ini.

### Fix prioritas banner di halaman Billing

State "ada pending yang belum selesai" harus jadi **prioritas paling atas**, mengalahkan banner "≤7 hari lagi" — supaya user nggak dikasih pesan yang salah konteks (nyuruh "perpanjang" padahal sebenarnya ada pembayaran yang tinggal diselesaikan).

```php
$pending = auth()->user()->subscriptions()->where('status', 'pending')
    ->where('created_at', '>', now()->subHours(24))->latest()->first();
$activeSubscription = auth()->user()->activeSubscription();
$daysRemaining = $activeSubscription ? now()->diffInDays($activeSubscription->expires_at, false) : null;
```

| Prioritas     | Kondisi                             | Banner                                                                             |
| ------------- | ----------------------------------- | ---------------------------------------------------------------------------------- |
| 1 (tertinggi) | Ada `$pending` valid                | "Kamu punya pembayaran yang belum diselesaikan" + tombol **Selesaikan Pembayaran** |
| 2             | Tidak ada pending, tidak ada active | "Kamu masih Free" + Upgrade                                                        |
| 3             | Aktif, `daysRemaining > 7`          | Info biasa, tanpa tombol                                                           |
| 4             | Aktif, `daysRemaining <= 7`         | Banner amber + Perpanjang Sekarang                                                 |
| 5             | Sudah expired                       | Banner merah + Perpanjang Langganan                                                |

```html
@if($pending)
<div
    class="border border-steel/40 bg-steel/5 rounded-sm p-4 flex items-center justify-between"
>
    <div>
        <p class="text-sm text-ink font-medium">
            Kamu punya pembayaran yang belum diselesaikan
        </p>
        <p class="font-mono text-xs text-ink-muted mt-1">
            Order #{{ $pending->midtrans_order_id }} · Rp {{
            number_format($pending->amount, 0, ',', '.') }}
        </p>
    </div>
    <a
        href="{{ route('checkout.renew') }}"
        class="bg-steel text-white font-medium px-4 py-2 rounded-sm text-sm whitespace-nowrap"
    >
        Selesaikan Pembayaran
    </a>
</div>
@endif
```

> Catatan soal URL: kamu sempat diarahkan ke `/pricing?action=checkout` — kalau memang itu tujuannya biar bypass pilihan paket, pastikan halaman itu langsung memanggil `initiateCheckout()` yang sudah diperbaiki di atas TANPA sempat menampilkan card pilihan paket sama sekali (harus terasa instan, bukan mampir dulu baru redirect). Boleh juga dirapikan jadi route terpisah `/checkout/renew` seperti di section 6 kalau lebih gampang dikontrol.

---

## 11. Definition of Done (tambahan)

- [ ] Klik "Perpanjang Sekarang" dua kali berturut-turut TANPA menyelesaikan pembayaran → transaksi pending TETAP SAMA (order_id sama), tidak bikin transaksi baru
- [ ] Kalau ada transaksi pending yang valid, banner "Selesaikan Pembayaran" muncul dan jadi prioritas — mengalahkan banner "≤7 hari lagi" kalau dua-duanya berpotensi muncul bersamaan
- [ ] Tombol "Selesaikan Pembayaran" membuka checkout dengan `snap_token` yang SAMA seperti percobaan sebelumnya, bukan generate baru

- [ ] Halaman `/` (guest) menampilkan landing page, bukan langsung redirect ke login (kecuali kamu memang mau balik ke redirect langsung — konfirmasi kalau mau diubah)
- [ ] Login & Register pakai layout split-panel sesuai desain sistem, bukan styling default lagi
- [ ] Halaman Pricing & Billing konsisten dengan token desain dashboard (hairline, mono untuk angka, amber untuk aksen)
- [ ] Checkout pakai `snap.embed`, TIDAK ada lagi popup Midtrans mengambang
- [ ] User dengan pending transaction yang klik "Upgrade" lagi TIDAK membuat transaksi baru — diarahkan menyelesaikan yang lama
- [ ] Halaman Billing melakukan pengecekan status ke Midtrans sebagai fallback kalau status masih `pending` (jaring pengaman webhook telat)
- [ ] Semua halaman ini responsive di mobile (khususnya form login/register — panel kiri disembunyikan otomatis di layar kecil)
- [ ] Tombol "Perpanjang Langganan" TIDAK muncul selama Pro masih aktif dengan sisa >7 hari — cuma muncul saat ≤7 hari lagi (sebagai peringatan) atau sudah expired
- [ ] Kapanpun tombol perpanjang itu muncul, langsung membuka checkout, TIDAK lagi mampir ke `/pricing`
- [ ] Begitu ada transaksi baru yang aktif, transaksi pending lain milik user yang sama otomatis ikut ditutup (jadi "Expired"), tidak ada lagi status "Pending" yang menggantung padahal user sudah Pro

---

## 12. Fix: Tombol "Batal" Harus Beneran Cancel di Midtrans + Hapus "Ganti Paket"

### A. Ganti label tombol

Karena cuma ada 1 paket berbayar (Pro), teks "Ganti Paket" tidak relevan — ganti jadi cuma **"Batal"** saja.

```html
<button
    type="submit"
    form="cancel-checkout-form"
    class="font-mono text-xs uppercase text-ink-muted underline hover:text-ink"
>
    Batal
</button>
```

### B. Klik "Batal" harus panggil Cancel API Midtrans, bukan cuma navigasi balik

```php
// routes/web.php
Route::post('/checkout/{subscription}/cancel', [CheckoutController::class, 'cancel'])
    ->middleware('auth')->name('checkout.cancel');
```

```php
public function cancel(Subscription $subscription)
{
    abort_if($subscription->user_id !== auth()->id(), 403);
    abort_unless($subscription->status === 'pending', 400); // cuma boleh cancel yang masih pending

    try {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
        \Midtrans\Transaction::cancel($subscription->midtrans_order_id);
    } catch (\Exception $e) {
        // kalau di Midtrans ternyata transaksinya sudah settlement/expired duluan
        // (race condition — misal user bayar tepat pas mau klik batal), abaikan error ini,
        // biar webhook yang urus status sebenarnya. Jangan sampai user macet gara-gara ini.
        \Log::warning('Gagal cancel transaksi Midtrans: ' . $e->getMessage());
    }

    $subscription->update(['status' => 'cancelled']);

    return redirect()->route('billing')->with('info', 'Pembayaran dibatalkan.');
}
```

```html
<!-- form pembungkus, ditaruh di halaman checkout -->
<form
    id="cancel-checkout-form"
    action="{{ route('checkout.cancel', $subscription) }}"
    method="POST"
    class="hidden"
>
    @csrf
</form>
```

> Catatan: pakai method `POST`, bukan `GET`, karena ini aksi yang mengubah data (bukan sekadar navigasi) — sesuaikan tombolnya jadi form submit, bukan `<a href>` biasa.

---

## 13. Definition of Done (tambahan)

- [ ] Buka dashboard Midtrans Sandbox, transaksi yang baru dibuat (belum dibayar) muncul dengan status "Pending" — dikonfirmasi manual, bukan diasumsikan
- [ ] Klik "Batal" pada checkout yang masih pending → transaksi di Midtrans Sandbox ikut berubah status (cancelled), dicek langsung dari dashboard Midtrans setelah klik
- [ ] Teks tombol sudah jadi "Batal" saja, tidak ada lagi "Ganti Paket"
