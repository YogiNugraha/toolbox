# 💳 ADDENDUM SPEC — Sistem Langganan (Free vs Pro) & Midtrans

> Addendum lanjutan dari `PROJECT_SPEC.md` + `PROJECT_SPEC_ADDENDUM_AUTH_DASHBOARD.md`. Jangan bongkar struktur yang sudah ada (auth, activity log, tools) — dokumen ini nambah **lapisan pembatasan akses** di atas sistem yang sudah jalan.

---

## 0. Aturan Bisnis

- **User Free** (default langsung setelah register, tanpa perlu bayar apapun):
  - Kuota harian TERBATAS per tools (reset tiap hari)
  - Beberapa fitur/preset dikunci (cuma bisa pakai versi dasar)
- **User Pro** (berlangganan, bayar via Midtrans):
  - Kuota unlimited di semua tools
  - Semua fitur/preset terbuka
  - Masa aktif 30 hari sejak bayar, lalu harus perpanjang manual (BUKAN auto-charge — lihat catatan di atas)

### Default limit (contoh awal — taruh di config, gampang diubah, bukan hardcode)

| Tools | Free | Pro |
|---|---|---|
| Compress Gambar | 5x/hari, preset "Sosial Media" & "Website" saja (preset "Custom" terkunci) | Unlimited, semua preset |
| Convert Format Gambar | 5x/hari, semua format tetap boleh | Unlimited |
| PDF ke Word | 2x/hari, max ukuran file 5MB | Unlimited, max 50MB |

> Angka-angka ini contoh awal, bisa diubah kapan saja tanpa ubah kode karena disimpan di config (lihat section 1).

---

## 1. Konfigurasi Plan — `config/plans.php`

Ikuti pola yang sudah dipakai di `config/tools.php` (config-driven, bukan hardcode di controller/component).

```php
return [
    'free' => [
        'name' => 'Free',
        'price' => 0,
        'limits' => [
            'compress-image' => [
                'daily_quota' => 5,
                'locked_features' => ['preset_custom'],
            ],
            'convert-image' => [
                'daily_quota' => 5,
                'locked_features' => [],
            ],
            'pdf-to-word' => [
                'daily_quota' => 2,
                'max_file_size_mb' => 5,
                'locked_features' => [],
            ],
        ],
    ],
    'pro' => [
        'name' => 'Pro',
        'price' => 49000, // per 30 hari, contoh — sesuaikan
        'duration_days' => 30,
        'midtrans_item_name' => 'ToolBox Pro - 30 Hari',
        'limits' => [
            'compress-image' => ['daily_quota' => null, 'locked_features' => []], // null = unlimited
            'convert-image' => ['daily_quota' => null, 'locked_features' => []],
            'pdf-to-word' => ['daily_quota' => null, 'max_file_size_mb' => 50, 'locked_features' => []],
        ],
    ],
];
```

---

## 2. Database

### Tabel `subscriptions`
```bash
php artisan make:model Subscription -m
```
```php
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('plan_slug');           // 'pro'
    $table->string('status');              // pending | active | expired | failed
    $table->timestamp('starts_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->string('midtrans_order_id')->unique()->nullable();
    $table->string('midtrans_transaction_id')->nullable();
    $table->unsignedInteger('amount')->nullable();
    $table->timestamps();
});
```

**Tidak perlu tabel usage counter baru** — tabel `activities` yang sudah ada (dari fitur logging sebelumnya) sudah cukup untuk hitung kuota harian, tinggal `COUNT` berdasarkan `user_id`, `tool_slug`, dan `created_at` hari ini.

### Model `Subscription`
```php
class Subscription extends Model
{
    protected $fillable = ['user_id', 'plan_slug', 'status', 'starts_at', 'expires_at', 'midtrans_order_id', 'midtrans_transaction_id', 'amount'];
    protected $casts = ['starts_at' => 'datetime', 'expires_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
}
```

### Relasi di `User`
```php
public function subscriptions() { return $this->hasMany(Subscription::class); }

public function activeSubscription()
{
    return $this->subscriptions()
        ->where('status', 'active')
        ->where('expires_at', '>', now())
        ->latest('expires_at')
        ->first();
}
```

---

## 3. `EntitlementService` — Otak Pengecekan Plan & Kuota

Ini service baru, dipanggil dari SETIAP Livewire tools component sebelum proses jalan.

```php
// app/Services/EntitlementService.php
class EntitlementService
{
    public function getCurrentPlan(User $user): string
    {
        return $user->activeSubscription() ? 'pro' : 'free';
    }

    public function getRemainingQuota(User $user, string $toolSlug): ?int
    {
        $plan = $this->getCurrentPlan($user);
        $limit = config("plans.{$plan}.limits.{$toolSlug}.daily_quota");

        if ($limit === null) return null; // unlimited

        $usedToday = \App\Models\Activity::where('user_id', $user->id)
            ->where('tool_slug', $toolSlug)
            ->whereDate('created_at', today())
            ->count();

        return max(0, $limit - $usedToday);
    }

    public function isFeatureLocked(User $user, string $toolSlug, string $featureKey): bool
    {
        $plan = $this->getCurrentPlan($user);
        $locked = config("plans.{$plan}.limits.{$toolSlug}.locked_features", []);
        return in_array($featureKey, $locked);
    }

    public function canProcessFile(User $user, string $toolSlug, int $fileSizeBytes): bool
    {
        $plan = $this->getCurrentPlan($user);
        $maxMb = config("plans.{$plan}.limits.{$toolSlug}.max_file_size_mb");
        if ($maxMb === null) return true;
        return $fileSizeBytes <= ($maxMb * 1024 * 1024);
    }
}
```

**Wajib dipanggil di setiap tools component** sebelum eksekusi proses:
```php
// contoh di ImageCompressor, sebelum compress dijalankan
$remaining = app(EntitlementService::class)->getRemainingQuota(auth()->user(), 'compress-image');

if ($remaining !== null && $remaining <= 0) {
    $this->quotaExceeded = true; // tampilkan paywall UI, JANGAN proses file
    return;
}

if ($this->selectedPreset === 'custom' && app(EntitlementService::class)->isFeatureLocked(auth()->user(), 'compress-image', 'preset_custom')) {
    $this->featureLocked = true; // tampilkan paywall UI
    return;
}
```

---

## 4. UI — Indikator Kuota & Paywall

### Indikator kuota (tampil di tiap halaman tools, bukan cuma pas limit habis)
```html
@if($remainingQuota !== null)
<div class="font-mono text-xs text-ink-muted border border-hairline rounded-sm px-3 py-1.5 inline-block mb-4">
    Sisa kuota hari ini: <span class="text-ink font-medium">{{ $remainingQuota }}</span> / {{ $dailyLimit }}
</div>
@endif
```

### Paywall inline (muncul menggantikan area upload, bukan popup mengganggu)
```html
<div class="border border-amber/30 bg-amber/5 rounded-sm p-8 text-center">
    <p class="font-display font-bold text-lg text-ink mb-2">Kuota harian kamu sudah habis</p>
    <p class="text-ink-muted text-sm mb-4">Upgrade ke Pro untuk pemakaian unlimited di semua tools.</p>
    <a href="{{ route('pricing') }}" class="bg-amber text-ink font-medium px-5 py-2.5 rounded-sm inline-block">
        Upgrade ke Pro
    </a>
</div>
```
Untuk fitur terkunci (misal preset "Custom"), style tab/opsi itu abu-abu/disabled dengan badge kecil "PRO" di sampingnya, klik → tampilkan pesan singkat + link upgrade, bukan langsung lempar ke halaman lain tanpa konteks.

### Halaman `/pricing`
- Tabel perbandingan Free vs Pro (sesuai section 0)
- Tombol "Upgrade Sekarang" untuk Pro → trigger Midtrans Snap (section 5)
- Kalau user sudah Pro, tombolnya berubah jadi info "Aktif sampai [tanggal]" + tombol "Perpanjang"

### Halaman Billing (di dalam `/profile` atau halaman baru `/billing`)
- Status plan sekarang (Free/Pro) + tanggal expired kalau Pro
- Riwayat transaksi sederhana (dari tabel `subscriptions`)

---

## 5. Integrasi Midtrans

### Setup
```bash
composer require midtrans/midtrans-php
```
```
# .env — pakai Sandbox key dulu, JANGAN production key selama development
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx
MIDTRANS_IS_PRODUCTION=false
```

### Flow Checkout (Snap)
```php
// saat user klik "Upgrade Sekarang"
\Midtrans\Config::$serverKey = config('services.midtrans.server_key');
\Midtrans\Config::$isProduction = config('services.midtrans.is_production');
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

$orderId = 'PRO-' . auth()->id() . '-' . time();

$subscription = Subscription::create([
    'user_id' => auth()->id(),
    'plan_slug' => 'pro',
    'status' => 'pending',
    'midtrans_order_id' => $orderId,
    'amount' => config('plans.pro.price'),
]);

$params = [
    'transaction_details' => [
        'order_id' => $orderId,
        'gross_amount' => config('plans.pro.price'),
    ],
    'customer_details' => [
        'first_name' => auth()->user()->name,
        'email' => auth()->user()->email,
    ],
    'item_details' => [[
        'id' => 'pro-30d',
        'price' => config('plans.pro.price'),
        'quantity' => 1,
        'name' => config('plans.pro.midtrans_item_name'),
    ]],
];

$snapToken = \Midtrans\Snap::getSnapToken($params);
// kirim $snapToken ke frontend, tampilkan pakai snap.js
```

Frontend (blade):
```html
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<button onclick="pay()">Bayar Sekarang</button>
<script>
function pay() {
    snap.pay('{{ $snapToken }}', {
        onSuccess: function(result) { window.location.href = '/billing?status=success'; },
        onPending: function(result) { window.location.href = '/billing?status=pending'; },
        onError: function(result) { alert('Pembayaran gagal, coba lagi.'); },
    });
}
</script>
```

### Webhook — INI YANG BENAR-BENAR MENGAKTIFKAN LANGGANAN
Jangan aktifkan Pro langsung dari `onSuccess` di JS (bisa dimanipulasi/gagal ditengah jalan) — aktivasi HARUS lewat webhook server-to-server dari Midtrans.

```php
// routes/web.php — kecualikan dari CSRF di bootstrap/app.php atau VerifyCsrfToken
Route::post('/webhooks/midtrans', [MidtransWebhookController::class, 'handle']);
```
```php
class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        // WAJIB verifikasi signature, jangan percaya payload mentah-mentah
        $signature = hash('sha512',
            $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . config('services.midtrans.server_key')
        );
        abort_if($signature !== $payload['signature_key'], 403);

        $subscription = Subscription::where('midtrans_order_id', $payload['order_id'])->firstOrFail();

        if (in_array($payload['transaction_status'], ['capture', 'settlement']) && ($payload['fraud_status'] ?? 'accept') === 'accept') {
            $subscription->update([
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addDays(config('plans.pro.duration_days')),
                'midtrans_transaction_id' => $payload['transaction_id'],
            ]);
        } elseif (in_array($payload['transaction_status'], ['deny', 'cancel', 'expire'])) {
            $subscription->update(['status' => 'failed']);
        }

        return response()->json(['status' => 'ok']);
    }
}
```

> **PENTING untuk development lokal:** Midtrans butuh URL yang bisa diakses dari internet buat kirim webhook — `localhost` TIDAK BISA diakses Midtrans. Wajib pakai tool tunneling seperti **ngrok** (`ngrok http 8000`) saat testing pembayaran, lalu daftarkan URL ngrok itu sebagai "Payment Notification URL" di dashboard Midtrans Sandbox. Tanpa ini, status pembayaran tidak akan pernah ter-update otomatis meskipun pembayaran sukses.

---

## 6. Urutan Pengerjaan (PENTING — ikuti urutan ini)

1. **Fase 1 — Gating tanpa payment dulu:** buat `config/plans.php`, tabel `subscriptions`, `EntitlementService`, integrasikan ke tiap tools component. Test dengan cara BUAT subscription manual lewat `php artisan tinker` (insert row status=active, expires_at 30 hari ke depan) untuk simulasikan user Pro — pastikan kuota, fitur lock, dan paywall UI semua berfungsi benar SEBELUM sentuh Midtrans sama sekali.
2. **Fase 2 — UI lengkap:** halaman `/pricing`, indikator kuota, paywall inline, halaman billing (tombol upgrade boleh sementara "coming soon"/disabled dulu).
3. **Fase 3 — Midtrans:** checkout Snap + webhook, pakai Sandbox key + ngrok untuk testing. Baru pindah ke production key setelah alur sandbox teruji lancar dari ujung ke ujung (bayar sandbox → webhook masuk → status jadi Pro → kuota jadi unlimited).

Jangan loncat ke Fase 3 sebelum Fase 1 benar-benar teruji — kalau logic gating salah, percuma payment-nya sukses tapi user tetap kena limit (atau sebaliknya, user Free malah dapat akses unlimited).

---

## 7. Definition of Done

- [ ] User baru daftar otomatis dapat plan Free (bukan Pro)
- [ ] Kuota harian tiap tools berkurang sesuai pemakaian & reset otomatis besok (dihitung dari `activities`, bukan counter terpisah yang bisa nggak sinkron)
- [ ] Fitur terkunci (preset Custom) benar-benar tidak bisa dipakai user Free, walau dipaksa lewat request manual sekalipun (validasi tetap di server, bukan cuma disable di UI)
- [ ] Paywall muncul dengan jelas & rapi (sesuai desain sistem) saat kuota habis atau fitur terkunci diklik
- [ ] Checkout Midtrans Sandbox berhasil dari klik "Upgrade" sampai status user berubah jadi Pro otomatis lewat webhook (bukan manual update)
- [ ] Setelah masa Pro habis (`expires_at` lewat), user otomatis kembali diperlakukan sebagai Free tanpa perlu intervensi manual
- [ ] Webhook memverifikasi signature Midtrans sebelum mengaktifkan langganan (tidak bisa dipalsukan dengan POST request sembarangan)
