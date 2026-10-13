@extends('layouts.dashboard')

@section('title', 'Fitur Khusus PRO - ' . $tool->name)
@section('page_title', $tool->name)
@section('page_breadcrumb', $tool->name)

@section('content')
<div class="flex min-h-[65vh] flex-col items-center justify-center p-4 text-center">
    <div class="card max-w-xl p-6 sm:p-8 border border-slate-150 dark:border-navy-600">
        {{-- Lock Icon --}}
        <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-warning/10 text-warning dark:bg-warning/20 mb-4">
            <x-lucide-lock class="size-8 stroke-[1.75]" />
        </div>

        {{-- Title & Message --}}
        <h2 class="text-xl sm:text-2xl font-semibold tracking-wide text-slate-800 dark:text-navy-100">
            Tool {{ $tool->name }} Terkunci
        </h2>

        <p class="mt-2 text-xs sm:text-sm text-slate-500 dark:text-navy-300 max-w-md mx-auto leading-relaxed">
            Alat bantu ini dirancang khusus untuk memproses tugas tingkat lanjut. Tingkatkan akun Anda ke paket <strong>PRO</strong> untuk membuka akses penuh ke tool ini.
        </p>

        {{-- 4 Key Benefit Highlights --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 my-6 text-left">
            <div class="flex items-start space-x-3 rounded-lg border border-slate-150 bg-slate-50/70 p-3 dark:border-navy-600 dark:bg-navy-800/60">
                <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-success/10 text-success mt-0.5">
                    <x-lucide-infinity class="size-4 stroke-[2]" />
                </div>
                <div>
                    <h4 class="text-xs font-medium text-slate-700 dark:text-navy-100">Bebas Kuota Harian</h4>
                    <p class="text-xs text-slate-500 dark:text-navy-300 mt-0.5">Proses berkas tanpa batasan limit harian.</p>
                </div>
            </div>

            <div class="flex items-start space-x-3 rounded-lg border border-slate-150 bg-slate-50/70 p-3 dark:border-navy-600 dark:bg-navy-800/60">
                <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light mt-0.5">
                    <x-lucide-file-up class="size-4 stroke-[2]" />
                </div>
                <div>
                    <h4 class="text-xs font-medium text-slate-700 dark:text-navy-100">Ukuran File Ekstra Besar</h4>
                    <p class="text-xs text-slate-500 dark:text-navy-300 mt-0.5">Dukungan kapasitas berkas hingga puluhan MB.</p>
                </div>
            </div>

            <div class="flex items-start space-x-3 rounded-lg border border-slate-150 bg-slate-50/70 p-3 dark:border-navy-600 dark:bg-navy-800/60">
                <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-warning/10 text-warning mt-0.5">
                    <x-lucide-zap class="size-4 stroke-[2]" />
                </div>
                <div>
                    <h4 class="text-xs font-medium text-slate-700 dark:text-navy-100">Pemrosesan Prioritas</h4>
                    <p class="text-xs text-slate-500 dark:text-navy-300 mt-0.5">Antrean proses diprioritaskan di server.</p>
                </div>
            </div>

            <div class="flex items-start space-x-3 rounded-lg border border-slate-150 bg-slate-50/70 p-3 dark:border-navy-600 dark:bg-navy-800/60">
                <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-info/10 text-info mt-0.5">
                    <x-lucide-unlock class="size-4 stroke-[2]" />
                </div>
                <div>
                    <h4 class="text-xs font-medium text-slate-700 dark:text-navy-100">Akses Semua Fitur Pro</h4>
                    <p class="text-xs text-slate-500 dark:text-navy-300 mt-0.5">Buka seluruh alat konversi dan kompresi.</p>
                </div>
            </div>
        </div>

        {{-- Action CTA Buttons --}}
        <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
            <a 
                href="{{ route('pricing') }}" wire:navigate
                class="btn h-9 rounded-full bg-primary px-5 text-xs font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus flex items-center gap-2"
            >
                <x-lucide-zap class="size-4" />
                <span>Upgrade ke PRO</span>
            </a>
            <a 
                href="{{ route('dashboard') }}" wire:navigate
                class="btn h-9 rounded-full border border-slate-300 px-5 text-xs font-medium text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-600 flex items-center gap-1.5"
            >
                <x-lucide-arrow-left class="size-3.5" />
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>
</div>
@endsection
