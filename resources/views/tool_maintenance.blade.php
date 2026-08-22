@extends('layouts.dashboard')

@php
    $catName = $tool->category ?? 'Tools';
    $catSlug = \Illuminate\Support\Str::slug($catName);
    $displayName = $catName;
    if (strtolower($catName) === 'image') $displayName = 'Gambar & Foto';
    if (strtolower($catName) === 'document') $displayName = 'Dokumen & PDF';
@endphp

@section('title', 'Pemeliharaan Layanan - ' . $tool->name)
@section('page_title', $tool->name)

@section('breadcrumb_parent')
    <li class="flex items-center space-x-2">
        <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('dashboard.category', $catSlug) }}">{{ $displayName }}</a>
        <svg x-ignore xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </li>
@endsection

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
