<x-mail::message>
# Berhenti Berlangganan 🛑

Halo **{{ $user->name }}**,

Kami menginformasikan bahwa paket langganan **ToolBox Pro** Anda telah berhasil diberhentikan sesuai permintaan Anda.

Mulai saat ini, akses ke fitur premium ToolBox telah dihentikan, dan status akun Anda kembali menjadi pengguna **Free**. Anda tetap dapat menggunakan layanan dasar kami seperti biasa.

Jika Anda berhenti karena menemui kendala atau memiliki masukan, kami sangat menghargai apabila Anda bersedia membalas email ini dan membagikan pengalaman Anda.

<x-mail::button :url="route('dashboard')">
Kembali ke ToolBox
</x-mail::button>

Terima kasih telah menggunakan ToolBox Pro. Kami berharap dapat melayani Anda kembali di masa mendatang!

Salam hangat,<br>
**Tim {{ config('app.name') }}**
</x-mail::message>
