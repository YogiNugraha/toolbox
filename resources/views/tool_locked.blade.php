@extends('layouts.dashboard')

@section('title', 'Fitur Khusus PRO - ' . $tool->name)
@section('page_title', $tool->name)
@section('page_breadcrumb', $tool->name)

@section('content')
<div class="flex min-h-[65vh] flex-col items-center justify-center p-4 text-center">
    <div class="card max-w-2xl p-6 sm:p-10 border border-purple-200/80 bg-white dark:border-purple-900/50 dark:bg-navy-700 shadow-xl rounded-2xl relative overflow-hidden">
        {{-- Subtle ambient blur glow in background --}}
        <div class="pointer-events-none absolute -top-16 -right-16 size-48 rounded-full bg-purple-500/10 blur-3xl dark:bg-purple-600/15"></div>
        <div class="pointer-events-none absolute -bottom-16 -left-16 size-48 rounded-full bg-amber-500/10 blur-3xl dark:bg-amber-600/15"></div>

        <div class="relative z-1">
            {{-- Crown & Lock Icon --}}
            <div class="relative mx-auto size-20 mb-5">
                <div class="mask is-squircle flex size-20 items-center justify-center bg-gradient-to-tr from-purple-600 via-indigo-600 to-amber-500 text-white shadow-lg shadow-purple-500/30">
                    <x-lucide-crown class="size-10 stroke-[2.2]" />
                </div>
                <div class="absolute -bottom-1 -right-1 flex size-7 items-center justify-center rounded-full bg-amber-400 text-slate-900 shadow-md ring-2 ring-white dark:ring-navy-700">
                    <x-lucide-lock class="size-3.5 stroke-[2.5]" />
                </div>
            </div>

            {{-- Badge --}}
            <span class="badge rounded-full bg-linear-to-r from-amber-500 via-purple-600 to-indigo-600 text-white text-[11px] font-black uppercase tracking-wider px-3.5 py-1 shadow-xs inline-flex items-center gap-1.5 mb-3">
                <x-lucide-sparkles class="size-3" />
                <span>Fitur Eksklusif Member PRO</span>
            </span>

            {{-- Title & Message --}}
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-navy-50 tracking-tight">
                Tool {{ $tool->name }} Terkunci
            </h2>

            <p class="mt-3 text-xs sm:text-sm text-slate-500 dark:text-navy-300 max-w-lg mx-auto leading-relaxed">
                Alat bantu ini dirancang khusus untuk memproses tugas-tugas tingkat lanjut. Tingkatkan akun Anda ke paket berlangganan <strong>PRO</strong> untuk membuka akses penuh ke tool ini.
            </p>

            {{-- 4 Key Benefit Highlights --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 my-7 text-left">
                <div class="flex items-start space-x-3 rounded-xl border border-slate-150 bg-slate-50/70 p-3.5 dark:border-navy-600 dark:bg-navy-800/60">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-success/15 text-success mt-0.5">
                        <x-lucide-infinity class="size-4 stroke-[2.5]" />
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-navy-100">Bebas Kuota Harian</h4>
                        <p class="text-[11px] text-slate-400 dark:text-navy-300 mt-0.5">Proses file tanpa batas 5 file/hari.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3 rounded-xl border border-slate-150 bg-slate-50/70 p-3.5 dark:border-navy-600 dark:bg-navy-800/60">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary/15 text-primary dark:bg-accent/15 dark:text-accent-light mt-0.5">
                        <x-lucide-file-up class="size-4 stroke-[2.5]" />
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-navy-100">Ukuran File Ekstra Besar</h4>
                        <p class="text-[11px] text-slate-400 dark:text-navy-300 mt-0.5">Dukungan kapasitas hingga ratusan MB.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3 rounded-xl border border-slate-150 bg-slate-50/70 p-3.5 dark:border-navy-600 dark:bg-navy-800/60">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-warning/15 text-warning mt-0.5">
                        <x-lucide-zap class="size-4 stroke-[2.5]" />
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-navy-100">Pemrosesan Prioritas Cepat</h4>
                        <p class="text-[11px] text-slate-400 dark:text-navy-300 mt-0.5">Server khusus antrean prioritas.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3 rounded-xl border border-slate-150 bg-slate-50/70 p-3.5 dark:border-navy-600 dark:bg-navy-800/60">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-purple-500/15 text-purple-600 dark:text-purple-400 mt-0.5">
                        <x-lucide-unlock class="size-4 stroke-[2.5]" />
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-navy-100">Akses Semua Tool Pro</h4>
                        <p class="text-[11px] text-slate-400 dark:text-navy-300 mt-0.5">Buka seluruh fitur premium tanpa terkunci.</p>
                    </div>
                </div>
            </div>

            {{-- Action CTA Buttons --}}
            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                <a 
                    href="{{ route('pricing') }}" wire:navigate
                    class="btn rounded-full bg-linear-to-r from-amber-500 via-purple-600 to-indigo-600 px-6 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-lg shadow-purple-500/30 hover:opacity-95 active:scale-95 transition-all flex items-center gap-2"
                >
                    <x-lucide-crown class="size-4 stroke-[2.5]" />
                    <span>Upgrade ke PRO Sekarang</span>
                </a>
                <a 
                    href="{{ route('dashboard') }}" wire:navigate
                    class="btn rounded-full border border-slate-300 px-5 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-150 dark:border-navy-500 dark:text-navy-100 dark:hover:bg-navy-600 flex items-center gap-1.5"
                >
                    <x-lucide-arrow-left class="size-3.5" />
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
