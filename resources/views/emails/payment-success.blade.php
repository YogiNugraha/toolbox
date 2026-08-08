<x-mail::message>
# Pembayaran Berhasil! 🎉

Halo **{{ $subscription->user->name }}**,

Terima kasih! Pembayaran Anda untuk langganan **ToolBox {{ ucfirst($subscription->plan_slug) }}** telah berhasil kami proses. 

Akun Anda sekarang telah ditingkatkan ke status Pro. Mulai saat ini, Anda dapat menikmati **semua fitur unggulan tanpa batasan**!

<x-mail::panel>
**Rincian Transaksi:**
- **Order ID:** {{ $subscription->midtrans_order_id }}
- **Tanggal:** {{ $subscription->created_at->translatedFormat('d F Y, H:i') }}
- **Total Pembayaran:** Rp {{ number_format($subscription->amount, 0, ',', '.') }}
- **Masa Aktif Hingga:** {{ $subscription->expires_at->translatedFormat('d F Y, H:i') }}
</x-mail::panel>

*Catatan: Kami telah melampirkan salinan PDF Invoice resmi pada email ini untuk keperluan administrasi Anda.*

<x-mail::button :url="route('dashboard')">
Mulai Gunakan ToolBox Pro
</x-mail::button>

Jika Anda memiliki pertanyaan lebih lanjut, jangan ragu untuk membalas email ini atau menghubungi tim dukungan kami.

Selamat berkreasi dan tingkatkan produktivitas Anda bersama **{{ config('app.name') }}**!

Salam hangat,<br>
**Tim {{ config('app.name') }}**
</x-mail::message>
