<x-mail::message>
# Pembayaran Berhasil! 🎉

Halo **{{ $subscription->user->name }}**,

Terima kasih, pembayaran Anda untuk paket **{{ ucfirst($subscription->plan_slug) }}** telah berhasil kami terima.
Akun Anda sekarang telah ditingkatkan ke status Pro dan Anda dapat menikmati semua fitur tanpa batasan.

### Rincian Transaksi
- **Order ID:** {{ $subscription->midtrans_order_id }}
- **Tanggal:** {{ $subscription->created_at->translatedFormat('d F Y, H:i') }}
- **Total Pembayaran:** Rp {{ number_format($subscription->amount, 0, ',', '.') }}
- **Masa Aktif Hingga:** {{ $subscription->expires_at->translatedFormat('d F Y, H:i') }}

<x-mail::button :url="route('dashboard.invoice', ['order_id' => $subscription->midtrans_order_id])" color="success">
Lihat / Cetak Invoice
</x-mail::button>

Jika Anda membutuhkan bantuan, jangan ragu untuk membalas email ini atau hubungi tim dukungan kami.

Terima kasih,<br>
Tim {{ config('app.name') }}
</x-mail::message>
