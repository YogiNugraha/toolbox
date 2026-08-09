<x-mail::message>
# Konfirmasi Perubahan Email

Halo {{ $user->name }},

Kami menerima permintaan untuk mengganti alamat email akun Anda. Silakan klik tombol di bawah ini untuk mengonfirmasi bahwa ini adalah alamat email baru Anda.

<x-mail::button :url="$url">
Konfirmasi Email Baru
</x-mail::button>

Jika Anda tidak merasa meminta perubahan email ini, Anda dapat mengabaikan email ini.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
