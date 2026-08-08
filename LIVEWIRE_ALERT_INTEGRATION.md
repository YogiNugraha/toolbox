# 🔔 ADDENDUM SPEC — Integrasi Livewire Alert (SweetAlert2)

> Package: [jantinnerezo/livewire-alert](https://github.com/jantinnerezo/livewire-alert) — wrapper SweetAlert2 untuk Livewire 3. Dipakai untuk 2 hal: (1) dialog konfirmasi sebelum aksi penting (batal langganan, dst), dan (2) toast notifikasi setelah aksi selesai (compress berhasil, upload gagal, dst) — ini sekaligus menutup gap "notifikasi/toast" yang dari dulu belum dikerjakan.

---

## 1. Instalasi

```bash
composer require jantinnerezo/livewire-alert
php artisan vendor:publish --tag=livewire-alert-config
```

Tambahkan komponen alert ke layout utama (SEKALI SAJA, di layout paling induk yang dipakai semua halaman — `layouts/app.blade.php` atau `layouts/dashboard.blade.php`):
```html
<!-- taruh sebelum tag penutup </body> -->
<livewire:livewire-alert />
```

Di tiap Livewire component yang mau pakai alert/dialog, tambahkan trait:
```php
use Jantinnerezo\LivewireAlert\LivewireAlert;

class Billing extends Component
{
    use LivewireAlert;
    // ...
}
```

---

## 2. WAJIB — Styling Supaya Nyambung ke Design System, Bukan Tampilan Default SweetAlert2

Default SweetAlert2 itu rounded penuh + warna biru generik — HARUS di-override supaya konsisten sama sistem desain kita (hairline border, amber, font display/mono). Edit `config/livewire-alert.php` hasil publish:

```php
'options' => [
    'buttonsStyling' => false, // WAJIB false, biar customClass di bawah ini yang kepake, bukan style bawaan SweetAlert2
    'customClass' => [
        'popup' => 'rounded-sm border border-hairline font-sans shadow-none',
        'title' => 'font-display font-bold text-ink text-lg',
        'htmlContainer' => 'text-ink-muted text-sm',
        'confirmButton' => 'bg-amber text-ink font-medium px-4 py-2 rounded-sm text-sm mx-1',
        'cancelButton' => 'border border-hairline text-ink-muted px-4 py-2 rounded-sm text-sm mx-1 bg-white',
    ],
],
```

---

## 3. Dialog Konfirmasi — Pasang di Aksi-Aksi Penting yang Belum Ada Konfirmasi

### A. Berhenti Berlangganan (paling wajib — ini aksi merugikan kalau kepencet nggak sengaja)
```php
// app/Livewire/Dashboard/Billing.php
use Jantinnerezo\LivewireAlert\LivewireAlert;

class Billing extends Component
{
    use LivewireAlert;

    public function confirmCancelSubscription()
    {
        $this->alert('warning', 'Yakin mau berhenti berlangganan Pro?', [
            'toast' => false,
            'position' => 'center',
            'showConfirmButton' => true,
            'confirmButtonText' => 'Ya, Berhenti',
            'showCancelButton' => true,
            'cancelButtonText' => 'Batal',
            'onConfirmed' => 'cancelSubscription', // method ini yang jalanin logic asli
        ]);
    }

    public function cancelSubscription()
    {
        // logic yang SUDAH ADA (ubah status expired + kirim email pembatalan)
        // ...

        $this->alert('success', 'Langganan Pro sudah dibatalkan.');
    }
}
```
```html
<!-- ganti wire:click yang langsung eksekusi, jadi manggil confirm dulu -->
<button wire:click="confirmCancelSubscription" class="...">Berhenti Berlangganan</button>
```

### B. Batal Checkout Pending (dari `FRONTEND_CHECKOUT_POLISH.md` section 12)
```php
public function confirmCancelCheckout()
{
    $this->alert('warning', 'Batalkan pembayaran ini?', [
        'showCancelButton' => true,
        'confirmButtonText' => 'Ya, Batalkan',
        'cancelButtonText' => 'Tidak',
        'onConfirmed' => 'cancel', // method cancel() yang sudah manggil Midtrans Cancel API
    ]);
}
```

---

## 4. Toast Notifikasi — Setelah Aksi Selesai (Nutup Gap UX Lama)

Pasang di titik-titik ini, semua pakai `toast: true` biar muncul kecil di pojok, tidak mengganggu (auto-hilang, tidak perlu diklik):

| Lokasi | Kapan | Contoh |
|---|---|---|
| `ImageCompressor` | Setelah compress berhasil | `$this->alert('success', 'Gambar berhasil dikompres!', ['toast' => true, 'position' => 'top-end', 'timer' => 3000]);` |
| `ImageCompressor` / `ImageConverter` / `PdfToWord` | Upload ditolak (validasi gagal) | `$this->alert('error', 'Format file tidak didukung.', ['toast' => true, 'position' => 'top-end', 'timer' => 4000]);` |
| Semua tools | Kuota harian habis | `$this->alert('info', 'Kuota harian kamu sudah habis.', ['toast' => true, 'position' => 'top-end']);` |
| `Profile` | Setelah simpan profil/ganti foto | `$this->alert('success', 'Profil berhasil diperbarui.', ['toast' => true, 'position' => 'top-end', 'timer' => 2500]);` |
| `History` | Setelah export Excel selesai | `$this->alert('success', 'File Excel siap diunduh.', ['toast' => true, 'position' => 'top-end']);` |
| `Billing` | Saat kembali dari checkout dengan `?status=success` di URL | `$this->alert('success', 'Pembayaran berhasil! Akun kamu sudah Pro.', ['toast' => true, 'position' => 'top-end', 'timer' => 4000]);` (cek query param di `mount()`) |
| `Billing` | Saat kembali dengan `?status=pending` | `$this->alert('info', 'Pembayaran sedang diproses.', ['toast' => true, 'position' => 'top-end']);` |

Contoh cek query param di `mount()`:
```php
public function mount()
{
    if (request('status') === 'success') {
        $this->alert('success', 'Pembayaran berhasil! Akun kamu sudah Pro.', ['toast' => true, 'position' => 'top-end', 'timer' => 4000]);
    } elseif (request('status') === 'pending') {
        $this->alert('info', 'Pembayaran sedang diproses.', ['toast' => true, 'position' => 'top-end']);
    }
}
```

---

## 5. Yang JANGAN Diganti Pakai Toast

- **Jangan** ganti banner status di Billing (Pro aktif / ≤7 hari / expired / pending) jadi toast — itu memang harus tetap terlihat terus selama halaman dibuka, bukan notifikasi sekilas yang hilang sendiri.
- **Jangan** pakai toast untuk error yang butuh penjelasan panjang (misal PDF gagal convert karena file corrupt) — itu tetap pakai pesan error inline di dekat tombol/upload area seperti sebelumnya, karena toast kecil kurang cukup ruang buat penjelasan.

---

## 6. Definition of Done

- [ ] Styling SweetAlert2 sudah konsisten dengan design system (hairline border, amber button, font display) — bukan tampilan default biru bulat
- [ ] Klik "Berhenti Berlangganan" memunculkan dialog konfirmasi dulu, tidak langsung eksekusi
- [ ] Klik "Batal" di checkout pending memunculkan dialog konfirmasi dulu
- [ ] Toast muncul otomatis setelah: compress/convert/pdf berhasil, upload ditolak, kuota habis, profil tersimpan, export Excel selesai, kembali dari pembayaran (success/pending)
- [ ] Toast otomatis hilang sendiri (tidak menumpuk kalau user melakukan beberapa aksi cepat berturut-turut)
