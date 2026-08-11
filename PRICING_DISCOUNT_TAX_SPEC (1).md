# 💰 ADDENDUM SPEC — Diskon Paket, Biaya Layanan, & Pajak

> Lanjutan dari `ADMIN_DYNAMIC_PLANS_SPEC.md`. Asumsi yang dipakai (lihat penjelasan di percakapan): diskon otomatis per paket (bukan kode kupon), biaya layanan & pajak berlaku global untuk semua paket.
>
> ⚠️ Ini mengubah cara `amount` dihitung saat checkout — sentuh langsung jalur yang sudah di-hardening. Jangan lupa regression test di section 6.

---

## 1. Tambahan Kolom di Tabel `plans` — Diskon

```php
Schema::table('plans', function (Blueprint $table) {
    $table->enum('discount_type', ['none', 'percent', 'fixed'])->default('none');
    $table->unsignedInteger('discount_value')->default(0); // persen (0-100) kalau type=percent, Rupiah kalau type=fixed
});
```

---

## 2. Tabel `settings` — Biaya Layanan & Pajak (Global)

```php
Schema::create('settings', function (Blueprint $table) {
    $table->string('key')->primary();
    $table->string('value')->nullable();
    $table->timestamps();
});
```
```php
class Setting extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, $value)
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
```
**Seed default:**
```php
Setting::set('tax_percent', '11');            // PPN, sesuaikan
Setting::set('service_fee_type', 'fixed');    // 'fixed' atau 'percent'
Setting::set('service_fee_value', '2500');
```

---

## 3. `PriceCalculator` — Satu Sumber Kebenaran Perhitungan Harga

**PENTING:** jangan hitung breakdown harga di banyak tempat berbeda (Pricing page, checkout, invoice) dengan logic masing-masing — bikin SATU service, dipakai di semua tempat, supaya angka yang ditampilkan ke user dan yang ditagih ke Midtrans selalu identik.

```php
class PriceCalculator
{
    public function breakdown(Plan $plan): array
    {
        $basePrice = $plan->price;

        $discount = match ($plan->discount_type) {
            'percent' => (int) round($basePrice * $plan->discount_value / 100),
            'fixed' => min($plan->discount_value, $basePrice), // diskon nggak boleh lebih besar dari harga
            default => 0,
        };

        $subtotal = $basePrice - $discount;

        $feeType = Setting::get('service_fee_type', 'fixed');
        $feeValue = (int) Setting::get('service_fee_value', 0);
        $serviceFee = $feeType === 'percent'
            ? (int) round($subtotal * $feeValue / 100)
            : $feeValue;

        $taxPercent = (int) Setting::get('tax_percent', 0);
        $tax = (int) round(($subtotal + $serviceFee) * $taxPercent / 100);

        $total = $subtotal + $serviceFee + $tax;

        return compact('basePrice', 'discount', 'subtotal', 'serviceFee', 'tax', 'taxPercent', 'total');
    }
}
```

---

## 4. Checkout — `amount` yang Ditagih Harus Pakai Hasil `total`, Bukan `plan->price` Lagi

Di `initiateCheckout()` (dari `SUBSCRIPTION_SPEC.md` / `ADMIN_DYNAMIC_PLANS_SPEC.md` section 4), ganti:
```php
$breakdown = app(PriceCalculator::class)->breakdown($plan);

$subscription = Subscription::create([
    'user_id' => $user->id,
    'plan_id' => $plan->id,
    'status' => 'pending',
    'amount' => $breakdown['total'], // BUKAN $plan->price lagi
    'midtrans_order_id' => $orderId,
]);
```

### Kirim ke Midtrans — pakai SATU item_detail dengan total akhir, jangan pecah banyak baris
Midtrans memvalidasi ketat bahwa jumlah `item_details` harus PERSIS SAMA dengan `gross_amount` — daripada ribet mecah jadi banyak baris (harga, diskon negatif, biaya, pajak) dan resiko selisih pembulatan bikin API-nya reject, lebih aman kirim satu baris dengan nama deskriptif dan breakdown-nya cukup ditampilkan di halaman kita sendiri (Ringkasan Pesanan) yang kita kontrol penuh:
```php
'item_details' => [[
    'id' => $plan->slug,
    'price' => $breakdown['total'],
    'quantity' => 1,
    'name' => $plan->name . ' (termasuk pajak & biaya layanan)',
]],
```

> ⚠️ Perubahan ini HANYA berlaku untuk transaksi BARU. Jangan sentuh/hitung ulang `amount` pada subscription yang sudah `active`/`expired` sebelumnya — itu akan mengacaukan data historis/akuntansi yang sudah tercatat.

---

## 5. Tampilan Breakdown Harga

### A. Halaman Pricing — tampilkan harga asli dicoret kalau ada diskon
```html
@php $breakdown = app(\App\Services\PriceCalculator::class)->breakdown($plan); @endphp

<div class="font-mono">
    @if($breakdown['discount'] > 0)
        <span class="text-ink-muted line-through text-sm">Rp {{ number_format($breakdown['basePrice'],0,',','.') }}</span>
    @endif
    <p class="font-bold text-3xl text-ink">Rp {{ number_format($breakdown['total'],0,',','.') }}</p>
</div>
```

### B. Ringkasan Pesanan di Halaman Checkout (dari `FRONTEND_CHECKOUT_POLISH.md` section 4b) — perbarui jadi breakdown lengkap
```html
<div class="space-y-1.5 text-sm">
    <div class="flex justify-between">
        <span class="text-ink-muted">Harga Paket</span>
        <span class="font-mono">Rp {{ number_format($breakdown['basePrice'],0,',','.') }}</span>
    </div>
    @if($breakdown['discount'] > 0)
    <div class="flex justify-between text-green-600">
        <span>Diskon</span>
        <span class="font-mono">-Rp {{ number_format($breakdown['discount'],0,',','.') }}</span>
    </div>
    @endif
    @if($breakdown['serviceFee'] > 0)
    <div class="flex justify-between">
        <span class="text-ink-muted">Biaya Layanan</span>
        <span class="font-mono">Rp {{ number_format($breakdown['serviceFee'],0,',','.') }}</span>
    </div>
    @endif
    @if($breakdown['tax'] > 0)
    <div class="flex justify-between">
        <span class="text-ink-muted">Pajak ({{ $breakdown['taxPercent'] }}%)</span>
        <span class="font-mono">Rp {{ number_format($breakdown['tax'],0,',','.') }}</span>
    </div>
    @endif
    <div class="flex justify-between font-bold border-t border-hairline pt-2 mt-2">
        <span>Total</span>
        <span class="font-mono text-lg">Rp {{ number_format($breakdown['total'],0,',','.') }}</span>
    </div>
</div>
```

### C. Invoice PDF — breakdown yang sama juga ditampilkan di sana
Cek `resources/views/pdf/invoice.blade.php` (dari `HANDOVER_DOC.md`), tambahkan breakdown yang sama supaya invoice yang diterima user transparan soal komponen harga, bukan cuma nominal total polos.

---

## 6. Admin UI

### A. Form Plan (`/admin/plans`) — tambahan field diskon
- Dropdown "Jenis Diskon": Tidak Ada / Persentase / Nominal Tetap
- Input "Nilai Diskon" (muncul kondisional kalau jenis diskon dipilih, sembunyikan kalau "Tidak Ada")
- Preview kalkulasi live di sebelah form (opsional, nice to have): tampilkan harga akhir setelah diskon+biaya+pajak biar admin langsung lihat efeknya sebelum simpan

### B. Halaman baru `/admin/settings` — Pengaturan Global
```php
Route::get('/settings', \App\Livewire\Admin\Settings::class)->name('admin.settings');
```
Form sederhana:
- Pajak (%): input number
- Biaya Layanan: dropdown Tetap/Persentase + input nilai
- Tombol Simpan (pakai Livewire Alert buat konfirmasi + toast sukses)

---

## 7. ⚠️ Wajib — Regression Test Setelah Ini

Ini fase KEDUA yang menyentuh langsung jalur checkout/amount setelah `ADMIN_DYNAMIC_PLANS_SPEC.md`. Setelah dikerjakan:
1. Jalankan ulang skenario checkout dari `PAYMENT_QA_CHECKLIST.md` — pastikan `amount` yang tercatat di `subscriptions` SAMA PERSIS dengan yang tercatat di dashboard Midtrans Sandbox (termasuk pajak & biaya layanan, bukan cuma harga paket polos).
2. Coba set diskon 100% di satu paket (edge case) — pastikan `amount` minimal tidak menjadi negatif (biaya layanan & pajak tetap harus jalan wajar meskipun harga paket didiskon habis).
3. Coba ubah pengaturan pajak/biaya layanan setelah ada transaksi pending yang belum dibayar — pastikan transaksi pending LAMA tetap pakai angka yang tercatat saat checkout dibuat, TIDAK ikut berubah cuma karena setting global baru diganti.

---

## 8. Definition of Done

- [ ] Admin bisa set diskon (persen/nominal) per paket dari `/admin/plans`, dan harga di halaman Pricing otomatis menampilkan harga dicoret + harga setelah diskon
- [ ] Admin bisa atur pajak & biaya layanan global dari `/admin/settings`
- [ ] `amount` yang ditagih ke Midtrans dan tersimpan di `subscriptions` sudah termasuk diskon, biaya layanan, dan pajak — bukan cuma `plan->price` mentah
- [ ] Breakdown harga (harga paket, diskon, biaya layanan, pajak, total) tampil jelas di halaman Pricing, Checkout, dan Invoice PDF — angka di ketiganya harus identik
- [ ] Diskon 100% tidak membuat total jadi negatif atau nol tanpa biaya layanan/pajak
- [ ] Mengubah setting pajak/biaya layanan TIDAK mengubah nominal transaksi yang sudah pending/selesai sebelumnya
- [ ] Regression test `PAYMENT_QA_CHECKLIST.md` dijalankan ulang dan semua masih PASS
