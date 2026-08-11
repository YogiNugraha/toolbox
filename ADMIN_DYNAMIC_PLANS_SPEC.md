# 📦 ADDENDUM SPEC — Manajemen Paket (Plans) Dinamis dari Admin Panel

> Menutup tech debt #3 di `HANDOVER_DOC.md`: `config/plans.php` (statis, cuma Free/Pro hardcode) dipindah jadi tabel database yang bisa dikelola Admin (`ADMIN_PANEL_SPEC.md`). Pricing page jadi looping dinamis, bukan 2 card hardcode.
>
> ⚠️ **Ini menyentuh langsung `EntitlementService`, checkout flow, dan webhook** — bagian paling sensitif yang sudah kita keraskan lewat `PAYMENT_QA_CHECKLIST.md`. Kerjakan BERFASE sesuai urutan di section 7, jangan sekali gebrak semua langsung.

---

## 1. Tabel `plans`

```bash
php artisan make:model Plan -m
```
```php
Schema::create('plans', function (Blueprint $table) {
    $table->id();
    $table->string('slug')->unique();       // 'free', 'pro', 'enterprise', dst — jangan diubah setelah dibuat
    $table->string('name');
    $table->unsignedInteger('price')->default(0);       // dalam Rupiah, 0 = gratis
    $table->unsignedInteger('duration_days')->nullable(); // null = tidak pernah expired (buat Free)
    $table->text('description')->nullable();
    $table->json('limits')->nullable();      // struktur sama seperti config/plans.php lama, per tool_slug
    $table->boolean('is_default')->default(false); // plan otomatis buat user baru (harus cuma 1 yang true)
    $table->boolean('is_active')->default(true);   // admin bisa nonaktifkan tanpa hapus data historis
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();
});
```

**Struktur `limits` (JSON), contoh:**
```json
{
    "compress-image": {"daily_quota": 5, "locked_features": ["preset_custom"]},
    "convert-image": {"daily_quota": 5, "locked_features": []},
    "pdf-to-word": {"daily_quota": 2, "max_file_size_mb": 5, "locked_features": []}
}
```

### Model `Plan`
```php
class Plan extends Model
{
    protected $fillable = ['slug','name','price','duration_days','description','limits','is_default','is_active','sort_order'];
    protected $casts = ['limits' => 'array', 'is_default' => 'boolean', 'is_active' => 'boolean'];

    public function subscriptions() { return $this->hasMany(Subscription::class); }
}
```

### Seeder — pindahkan data dari `config/plans.php` yang lama, JANGAN hilang
```php
Plan::create([
    'slug' => 'free', 'name' => 'Free', 'price' => 0, 'duration_days' => null,
    'is_default' => true, 'is_active' => true, 'sort_order' => 1,
    'limits' => [ /* copy persis dari config/plans.php 'free' => 'limits' */ ],
]);

Plan::create([
    'slug' => 'pro', 'name' => 'Pro', 'price' => 49000, 'duration_days' => 30,
    'is_default' => false, 'is_active' => true, 'sort_order' => 2,
    'limits' => [ /* copy persis dari config/plans.php 'pro' => 'limits' */ ],
]);
```
Jalankan seeder ini SEKALI di database yang sudah ada — jangan sampai data lama hilang atau angkanya beda dari yang sekarang aktif dipakai.

---

## 2. Tabel `subscriptions` — Tambah Referensi ke `plans`

```php
Schema::table('subscriptions', function (Blueprint $table) {
    $table->foreignId('plan_id')->nullable()->after('plan_slug')->constrained();
});
```
**Backfill data lama:**
```php
// jalankan sekali via tinker/seeder, isi plan_id berdasarkan plan_slug yang sudah ada
Subscription::whereNull('plan_id')->each(function ($sub) {
    $plan = Plan::where('slug', $sub->plan_slug)->first();
    if ($plan) $sub->update(['plan_id' => $plan->id]);
});
```
Kolom `plan_slug` yang lama JANGAN dihapus dulu — biarkan tetap ada sebagai cadangan/kompatibilitas, cukup `plan_id` yang jadi sumber kebenaran baru ke depannya.

### Relasi di model `Subscription`
```php
public function plan() { return $this->belongsTo(Plan::class); }
```

---

## 3. `EntitlementService` — Baca dari Database, Bukan `config()` Lagi

Ini bagian PALING SENSITIF — dipakai di setiap tools buat cek kuota & fitur terkunci. Ubah dengan hati-hati, cari SEMUA pemanggil method-methodnya sebelum ubah signature.

```php
public function getCurrentPlan(User $user): Plan
{
    $sub = $user->activeSubscription();
    if ($sub && $sub->plan) {
        return $sub->plan;
    }
    return Plan::where('is_default', true)->firstOrFail();
}

public function getRemainingQuota(User $user, string $toolSlug): ?int
{
    $plan = $this->getCurrentPlan($user);
    $limit = $plan->limits[$toolSlug]['daily_quota'] ?? null;

    if ($limit === null) return null; // unlimited

    $usedToday = Activity::where('user_id', $user->id)
        ->where('tool_slug', $toolSlug)
        ->whereDate('created_at', today())
        ->count();

    return max(0, $limit - $usedToday);
}

public function isFeatureLocked(User $user, string $toolSlug, string $featureKey): bool
{
    $plan = $this->getCurrentPlan($user);
    $locked = $plan->limits[$toolSlug]['locked_features'] ?? [];
    return in_array($featureKey, $locked);
}

public function canProcessFile(User $user, string $toolSlug, int $fileSizeBytes): bool
{
    $plan = $this->getCurrentPlan($user);
    $maxMb = $plan->limits[$toolSlug]['max_file_size_mb'] ?? null;
    if ($maxMb === null) return true;
    return $fileSizeBytes <= ($maxMb * 1024 * 1024);
}
```

⚠️ **`getCurrentPlan()` sekarang return objek `Plan`, bukan string `'free'`/`'pro'` seperti sebelumnya.** Cari SEMUA tempat yang manggil method ini (`ImageCompressor`, `ImageConverter`, `PdfToWord`, `Pricing.php`, `Billing.php`, mungkin juga Admin Panel) dan pastikan kodenya disesuaikan — kalau ada yang masih membandingkan hasilnya dengan string `=== 'pro'`, itu akan diam-diam selalu false setelah perubahan ini dan bikin bug tersembunyi.

---

## 4. Checkout Flow — Generalisasi, Jangan Hardcode "Pro"

Cari logic checkout (`initiateCheckout()`, dari `SUBSCRIPTION_SPEC.md` & `FRONTEND_CHECKOUT_POLISH.md`) yang sekarang mengambil harga/durasi dari `config('plans.pro...')` — ganti supaya menerima parameter `Plan $plan` dan ambil semua datanya dari situ:

```php
public function initiateCheckout(User $user, Plan $plan)
{
    $pending = $user->subscriptions()
        ->where('status', 'pending')
        ->where('created_at', '>', now()->subHours(24))
        ->latest()->first();

    if ($pending && $pending->snap_token) {
        return $pending->snap_token;
    }
    if ($pending) {
        $pending->update(['status' => 'expired']);
    }

    $orderId = strtoupper($plan->slug) . '-' . $user->id . '-' . time();

    $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'plan_slug' => $plan->slug, // tetap isi buat kompatibilitas
        'status' => 'pending',
        'amount' => $plan->price,
        'midtrans_order_id' => $orderId,
    ]);

    // ...generate Snap token seperti biasa, pakai $plan->price & $plan->name
}
```
Dan di webhook handler, saat mengaktifkan subscription:
```php
$subscription->update([
    'status' => 'active',
    'starts_at' => now(),
    'expires_at' => $subscription->plan->duration_days
        ? now()->addDays($subscription->plan->duration_days)
        : null, // null = tidak pernah expired
]);
```

---

## 5. Admin Panel — Halaman "Kelola Paket" (`/admin/plans`)

Tambahan ke `layouts/admin.blade.php` sidebar: menu baru "Paket".

### List
Tabel: Nama, Slug, Harga, Durasi, Status (Aktif/Nonaktif), Default (badge kalau ini plan Free default), Urutan, Aksi (Edit/Hapus/Toggle Aktif).

### Form Create/Edit
- Nama, Harga, Durasi (hari — kosongkan utk "tidak pernah expired"), Deskripsi, Urutan tampil
- Slug: HANYA bisa diisi saat create, readonly saat edit (karena dipakai referensi di banyak tempat — ganti slug setelah ada subscription aktif akan mengacaukan data historis)
- Toggle "Jadikan paket default (Free)" — kalau diaktifkan di satu plan, OTOMATIS nonaktifkan di plan lain (harus selalu ada TEPAT 1 plan dengan `is_default = true`)
- Toggle "Aktif" — plan nonaktif tetap muncul di riwayat/data lama, tapi hilang dari halaman Pricing
- Section per-tool (loop dari `config('tools')`): untuk tiap tools, input Kuota Harian (kosongkan = unlimited) + checkbox fitur yang mau dikunci (kalau tools itu punya fitur terkunci-able) + khusus PDF ke Word, input tambahan Max Ukuran File (MB)

### Proteksi hapus
```php
public function deletePlan(Plan $plan)
{
    abort_if($plan->is_default, 400, 'Tidak bisa hapus paket default.');
    abort_if($plan->subscriptions()->exists(), 400, 'Tidak bisa hapus paket yang punya riwayat transaksi. Nonaktifkan saja.');

    $plan->delete();
}
```
Pakai Livewire Alert buat konfirmasi hapus (pola yang sama seperti Ban User di `ADMIN_PANEL_SPEC.md`).

---

## 6. Halaman Pricing — Jadi Dinamis (Looping, Bukan Hardcode)

```php
public function render()
{
    $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
    return view('livewire.pricing', compact('plans'));
}
```
```html
<div class="grid grid-cols-1 md:grid-cols-{{ min($plans->count(), 4) }} gap-6">
    @foreach($plans as $plan)
        <div class="border rounded-sm p-6 {{ $plan->id === $currentPlan?->id ? 'border-amber' : 'border-hairline' }}">
            <p class="uppercase text-xs font-mono text-ink-muted">{{ $plan->name }}</p>
            <p class="font-mono font-bold text-3xl text-ink">
                Rp {{ number_format($plan->price, 0, ',', '.') }}
                <span class="text-sm font-sans text-ink-muted">
                    / {{ $plan->duration_days ? $plan->duration_days . ' hr' : 'selamanya' }}
                </span>
            </p>
            <p class="text-ink-muted text-sm mb-4">{{ $plan->description }}</p>

            @foreach($plan->limits ?? [] as $toolSlug => $limit)
                <p class="text-sm flex items-center gap-2">
                    <span class="text-amber">✓</span>
                    {{ $limit['daily_quota'] === null ? 'Unlimited' : $limit['daily_quota'] . 'x/hari' }}
                    {{ config("tools.{$toolSlug}.name", $toolSlug) }}
                </p>
            @endforeach

            <button wire:click="selectPlan({{ $plan->id }})" class="...">
                {{ $plan->id === $currentPlan?->id ? 'Paket Aktif' : 'Pilih Paket' }}
            </button>
        </div>
    @endforeach
</div>
```

---

## 7. ⚠️ Urutan Pengerjaan — WAJIB Berfase, Jangan Sekaligus

1. **Fase 1:** Bikin tabel `plans` + seed data Free/Pro yang PERSIS SAMA dengan yang sekarang aktif di `config/plans.php`. Jangan sentuh kode lain dulu — tabel ini nganggur paralel dulu, sistem tetap jalan pakai config lama.
2. **Fase 2:** Tambah `plan_id` ke `subscriptions` + backfill data lama. Masih belum ada kode yang baca dari sini.
3. **Fase 3 (PALING BERISIKO):** Ubah `EntitlementService` baca dari DB. Setelah ini, test SEMUA tools (compress, convert, pdf) — pastikan kuota & fitur terkunci masih berjalan sama persis seperti sebelumnya untuk user Free & Pro yang sudah ada.
4. **Fase 4:** Generalisasi checkout flow & webhook supaya terima `Plan` dinamis. Test checkout Pro dari awal sampai aktif seperti biasa.
5. **Fase 5:** Bangun UI Admin "Kelola Paket" (CRUD).
6. **Fase 6:** Ubah halaman Pricing jadi looping dinamis.
7. **Fase 7 — WAJIB:** Jalankan ulang skenario-skenario kritis di `PAYMENT_QA_CHECKLIST.md` (terutama bagian C — bypass validasi, dan A — webhook idempotency) setelah semua fase di atas selesai, karena refactor ini menyentuh jalur yang sama persis dengan yang dites di situ.

---

## 8. Definition of Done

- [ ] Data plan Free & Pro yang lama TIDAK berubah nilainya setelah pindah ke database (harga, kuota, fitur terkunci — semua sama persis)
- [ ] User yang sudah punya subscription AKTIF sebelum migrasi ini tetap dianggap Pro dengan benar (dicek `plan_id` ter-backfill dengan tepat)
- [ ] Semua tools (compress, convert, pdf) masih menghormati kuota & fitur terkunci dengan benar setelah `EntitlementService` pindah baca dari DB
- [ ] Admin bisa membuat paket baru (misal "Enterprise") lewat `/admin/plans`, dan paket itu otomatis muncul di halaman Pricing tanpa perlu ubah kode
- [ ] Tidak bisa ada 2 paket dengan `is_default = true` di saat bersamaan
- [ ] Tidak bisa hapus paket yang masih punya riwayat subscription (harus dinonaktifkan saja)
- [ ] Checkout & webhook tetap berfungsi normal untuk paket manapun (dites minimal dengan paket Pro yang sudah ada DAN 1 paket baru yang dibuat lewat Admin)
- [ ] Regression test dari `PAYMENT_QA_CHECKLIST.md` dijalankan ulang dan semua masih PASS
