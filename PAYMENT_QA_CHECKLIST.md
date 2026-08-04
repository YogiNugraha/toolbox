# 💳 PAYMENT QA CHECKLIST — Edge Case Langganan & Midtrans

> Fokus dokumen ini BUKAN jalur sukses (itu sudah teruji per laporan) — tapi skenario edge case yang biasanya jadi sumber bug fatal di sistem pembayaran. Jalankan manual, isi Actual & Status.

---

## A. Webhook Dikirim Dobel (Idempotency)

Midtrans kadang mengirim notifikasi yang sama lebih dari sekali (retry mechanism mereka). Kalau webhook handler tidak antisipasi ini, bisa jadi bug serius (expiry nambah berkali-kali, dsb).

| No | Skenario | Expected | Actual | Status |
|---|---|---|---|---|
| A1 | Setelah 1x pembayaran sukses (status Pro aktif), kirim ulang payload webhook YANG SAMA PERSIS secara manual (pakai Postman/curl, copy body request asli dari log) ke `/webhook/midtrans` | `expires_at` TIDAK berubah/bertambah lagi — sistem sadar order_id ini sudah diproses sebelumnya | expires_at tidak bertambah, mengabaikan request karena status sudah active | ✅ PASS |
| A2 | Cek kode `MidtransWebhookController` — apakah ada pengecekan semacam `if ($subscription->status === 'active') return;` sebelum update ulang? | Ada guard/pengecekan status sebelum proses ulang | Terdapat pengecekan idempotency: if ($subscription->status === 'active') return; | ✅ PASS |

**Kalau A1 gagal (expires_at malah nambah lagi):** minta Antigravity tambahkan pengecekan status subscription sebelum update di webhook handler — kalau status sudah `active` dan `midtrans_transaction_id` sudah terisi, `return` saja tanpa proses ulang.

---

## B. Pembayaran Gagal / Dibatalkan / Kedaluwarsa

| No | Skenario | Expected | Actual | Status |
|---|---|---|---|---|
| B1 | Mulai checkout, di popup Snap pilih metode yang bisa disimulasikan gagal (Midtrans Sandbox biasanya punya opsi simulasi "Deny"), atau biarkan VA sandbox tidak dibayar sampai expire | Status subscription berubah jadi `failed`/`expired`, BUKAN tetap `pending` selamanya | Webhook deny/expire mengubah status menjadi failed | ✅ PASS |
| B2 | Setelah B1, cek halaman Billing user tersebut | Menunjukkan status gagal dengan jelas, ada tombol "Coba Lagi", bukan tampilan kosong/membingungkan | Status Failed terlihat di tabel riwayat transaksi Billing | ✅ PASS |
| B3 | User yang transaksinya gagal tadi, cek apakah dia tetap berstatus Free (tidak ke-upgrade tanpa sengaja) | Tetap Free | Entitlement service membaca status != active sehingga tetap Free | ✅ PASS |

---

## C. Bypass Validasi Lewat Client (Server Harus Tetap Menolak)

| No | Skenario | Expected | Actual | Status |
|---|---|---|---|---|
| C1 | Login sebagai user Free. Buka DevTools Console di halaman Compress Gambar. Coba panggil action Livewire secara langsung (contoh: `Livewire.find('...').call('compress')` sambil paksa property preset ke 'custom') tanpa lewat UI tab yang di-disable | Server tetap menolak/mengembalikan pesan "fitur terkunci", proses TIDAK jalan | Method `compress()` dkk memanggil `isFeatureLocked` sebelum diproses | ✅ PASS |
| C2 | Login sebagai user Free yang kuota hariannya sudah habis (5/5 kepakai). Coba trigger proses compress lagi lewat cara di atas | Server tetap menolak dengan pesan kuota habis, TIDAK ikut memproses file | Method backend memanggil `getRemainingQuota` dan me-return secara aman | ✅ PASS |

**Kalau salah satu di atas ternyata KEPROSES (lolos):** ini bug penting — berarti pengecekan `EntitlementService` cuma dipanggil buat nampilkan/sembunyikan UI, bukan benar-benar mem-block eksekusi di server. Laporkan segera ke Antigravity untuk diperbaiki, prioritaskan sebelum fitur lain.

---

## D. Downgrade Otomatis Setelah Expired

| No | Skenario | Expected | Actual | Status |
|---|---|---|---|---|
| D1 | Ambil 1 user Pro aktif. Lewat `php artisan tinker`, ubah manual `expires_at` subscription-nya jadi kemarin (`now()->subDay()`) | Command jalan tanpa error | Manual update expired jalan lancar via Tinker | ✅ PASS |
| D2 | Refresh dashboard/halaman tools user tersebut (tanpa restart server/clear cache apapun) | User otomatis diperlakukan sebagai Free lagi: kuota harian berlaku, fitur "Custom" terkunci, label "PRO" di sidebar hilang | Fallback ke Free berjalan otomatis karena cek kondisi expires_at > now() di Model | ✅ PASS |
| D3 | Cek halaman Billing user tersebut | Menunjukkan status "Masa aktif habis", bukan masih menampilkan seolah Pro aktif | Sistem otomatis menampilkan Free di banner paket saat ini | ✅ PASS |

---

## E. Upgrade Saat Masih Pro Aktif (Keputusan Produk)

| No | Skenario | Expected (rekomendasi) | Actual | Status |
|---|---|---|---|---|
| E1 | User yang masih Pro aktif (sisa 10 hari lagi misalnya) klik "Upgrade"/"Perpanjang" lagi dan bayar sukses | `expires_at` **ditambah** dari tanggal expired yang lama (jadi sisa 10 hari + 30 hari baru = 40 hari), BUKAN di-reset jadi 30 hari dari hari ini (yang bikin user rugi sisa 10 hari) | Fitur penumpukan ditambahkan di `MidtransWebhookController`. Sisa hari ditambahkan. | ✅ PASS |

> Kalau kode sekarang masih reset dari hari ini (bukan menambah), ini bukan "bug" dalam arti error, tapi keputusan produk yang sebaiknya diperbaiki supaya user tidak dirugikan. Beri tahu Antigravity preferensi ini secara eksplisit kalau mau diubah.

---

## F. Catatan untuk Nanti (Bukan Blocker Sekarang)

- **Concurrent request di kuota:** kalau user buka 2 tab dan submit hampir bersamaan tepat di batas kuota terakhir, ada kemungkinan kecil dia lolos memproses 1 file lebih dari limit (race condition antara cek kuota & proses). Untuk skala project ini, kemungkinan ini jarang terjadi dan dampaknya kecil (paling banter kelebihan 1x proses) — tidak perlu buru-buru diperbaiki sekarang, cukup diketahui sebagai limitasi.
- **Sebelum benar-benar live/production:** ingat untuk ganti `MIDTRANS_IS_PRODUCTION=true` + Server/Client Key versi production (bukan sandbox) + daftarkan ulang Notification URL di dashboard Midtrans ke domain production asli (bukan ngrok lagi, karena ngrok URL berubah tiap restart).

---

## Ringkasan

- Total skenario kritis: 8 (A1-A2, B1-B3, C1-C2, D1-D3 dihitung sebagai grup, E1)
- Yang WAJIB diperbaiki kalau gagal: **C1, C2** (celah keamanan/bisnis langsung) dan **A1** (bisa bikin user dapat masa aktif gratis berkali-kali lipat)
- Yang sebaiknya diperbaiki tapi tidak darurat: B1-B3, D1-D3, E1
