@extends('layouts.base')

@section('content')
<div class="min-h-screen flex font-sans">
  <!-- Panel kiri: brand, disembunyikan di mobile -->
  <div class="hidden lg:flex w-1/2 bg-ink text-white flex-col justify-between p-12">
    <span class="font-display font-bold text-xl"><a href="/">{{ config('app.name') }}</a></span>
    <div>
      <p class="font-display text-4xl font-bold mb-3 leading-tight">Berhasil.</p>
      <p class="text-slate-400 text-sm">Akun Anda siap digunakan sepenuhnya.</p>
    </div>
    <p class="font-mono text-xs text-slate-500">© {{ date('Y') }} {{ config('app.name') }}</p>
  </div>

  <!-- Panel kanan: message -->
  <div class="w-full lg:w-1/2 flex items-center justify-center bg-paper p-8">
    <div
        class="text-center max-w-sm mx-auto"
        x-data="{ seconds: 5 }"
        x-init="
            const t = setInterval(() => {
                seconds--;
                if (seconds <= 0) { clearInterval(t); window.location.href = '{{ route('dashboard') }}'; }
            }, 1000);
         "
    >
        <div class="text-green-600 text-4xl mb-4">✓</div>
        <h1 class="font-display font-bold text-2xl text-ink mb-2">
            Verifikasi Berhasil!
        </h1>
        <p class="text-ink-muted text-sm mb-6">
            Email kamu sudah terverifikasi. Diarahkan otomatis dalam
            <span x-text="seconds" class="font-mono font-medium text-ink"></span>
            detik.
        </p>
        <a
            href="{{ route('dashboard') }}"
            class="bg-amber text-ink font-medium px-6 py-2.5 rounded-sm inline-block shadow-sm hover:bg-amber/90 transition-colors"
        >
            Ke Dashboard Sekarang
        </a>
    </div>
  </div>
</div>
@endsection
