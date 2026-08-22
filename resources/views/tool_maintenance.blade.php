@extends('layouts.dashboard')

@section('title', 'Pemeliharaan Layanan - ' . $tool->name)
@section('page_title', $tool->name)
@section('page_breadcrumb', 'Pemeliharaan')

@section('content')
<div class="flex min-h-[60vh] flex-col items-center justify-center p-4 text-center">
    <div class="card max-w-lg p-6 sm:p-8">
        <div class="mask is-squircle mx-auto flex size-16 items-center justify-center bg-warning/15 text-warning dark:bg-warning/20 mb-5 shadow-sm">
            <x-lucide-hammer class="size-8" />
        </div>

        <span class="badge rounded-full bg-warning/10 text-warning text-xs font-bold px-3 py-1 uppercase tracking-wider mb-2">
            Sedang Dalam Pemeliharaan
        </span>

        <h3 class="text-xl font-bold text-slate-800 dark:text-navy-50 mt-2">
            Tool {{ $tool->name }} Sementara Nonaktif
        </h3>

        <p class="mt-3 text-xs sm:text-sm text-slate-500 dark:text-navy-300 leading-relaxed">
            {{ $tool->maintenance_message ?: 'Layanan ini sedang dalam peningkatan performa server atau pemeliharaan berkala untuk memberikan hasil konversi yang lebih baik. Silakan coba kembali beberapa saat lagi.' }}
        </p>

        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn h-9 rounded-full bg-primary px-5 text-xs font-bold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus shadow-sm flex items-center space-x-1.5">
                <x-lucide-arrow-left class="size-4" />
                <span>Kembali ke Dashboard</span>
            </a>
            <a href="{{ route('history') }}" class="btn h-9 rounded-full border border-slate-300 px-4 text-xs font-semibold text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                <span>Lihat Riwayat File</span>
            </a>
        </div>
    </div>
</div>
@endsection
