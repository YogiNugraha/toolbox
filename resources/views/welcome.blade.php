<x-base-layout is-header-blur="true" title="Compress, Convert, & Olah File Online Cepat">
    <div x-data="{ 
        search: '', 
        selectedCategory: 'all',
        faqOpen: null,
        tools: {{ json_encode($tools) }},
        categories: {{ json_encode($categories) }},
        get highlightedCount() {
            return this.tools.filter(t => t.is_highlighted).length;
        },
        get filteredTools() {
            return this.tools.filter(tool => {
                const matchSearch = !this.search || 
                                    tool.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                    (tool.description && tool.description.toLowerCase().includes(this.search.toLowerCase())) ||
                                    (tool.category && tool.category.toLowerCase().includes(this.search.toLowerCase()));
                
                let matchCategory = true;
                if (this.selectedCategory === 'highlighted') {
                    matchCategory = Boolean(tool.is_highlighted);
                } else if (this.selectedCategory !== 'all') {
                    matchCategory = tool.category.toLowerCase() === this.selectedCategory.toLowerCase();
                }

                return matchSearch && matchCategory;
            });
        }
    }" class="w-full min-h-screen flex flex-col justify-between bg-slate-50 dark:bg-navy-900 transition-colors duration-300">

        <div class="relative w-full">
            {{-- Navigation Header (Lineone Starter Blurred Header Style) --}}
            <header class="sticky top-0 z-40 w-full border-b border-slate-200/80 bg-white/90 backdrop-blur-md dark:border-navy-700/80 dark:bg-navy-900/90 transition-colors duration-300">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
                    @php
                        $siteName = \App\Models\Setting::get('site_name', \App\Models\Setting::get('brand_name', config('app.name')));
                        $siteTagline = \App\Models\Setting::get('site_tagline', \App\Models\Setting::get('brand_tagline', 'Online Web Tools'));
                        $siteLogo = \App\Models\Setting::get('site_logo');
                        $siteDesc = \App\Models\Setting::get('site_description', 'Solusi perkakas digital instan untuk mengolah, mengompres, dan mengonversi file Anda setiap hari tanpa instalasi software.');
                        $footerCopyright = \App\Models\Setting::get('footer_copyright', '© ' . date('Y') . ' ' . $siteName . '. All rights reserved.');
                    @endphp

                    {{-- Brand Logo --}}
                    <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                        @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                            <div class="flex size-10 shrink-0 items-center justify-center">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($siteLogo) }}" class="size-full object-contain" alt="{{ $siteName }}" />
                            </div>
                        @else
                            <div class="mask is-squircle flex size-10 shrink-0 items-center justify-center bg-primary text-white shadow-md shadow-primary/20 dark:bg-accent dark:shadow-accent/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                        @endif
                        <div class="flex flex-col">
                            <span class="text-base sm:text-lg font-bold tracking-tight text-slate-800 dark:text-navy-50 uppercase leading-none">
                                {{ $siteName }}
                            </span>
                            <span class="text-[10px] font-semibold tracking-widest text-slate-400 dark:text-navy-300 uppercase mt-0.5">
                                {{ $siteTagline }}
                            </span>
                        </div>
                    </a>

                    {{-- Navigation Links (Desktop) --}}
                    <nav class="hidden md:flex items-center space-x-6 lg:space-x-8 text-xs font-semibold text-slate-600 dark:text-navy-200">
                        <a href="{{ route('tools.index') }}" class="hover:text-primary dark:hover:text-accent-light transition-colors">Katalog Tools</a>
                        <a href="#features-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">Keunggulan</a>
                        <a href="#pricing-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">Paket & Harga</a>
                        <a href="#faq-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">FAQ</a>
                    </nav>

                    {{-- Right Actions: Dark Mode Switcher & Auth Buttons --}}
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        {{-- Dark Mode Switcher --}}
                        <button 
                            @click="$store.global.isDarkModeEnabled = !$store.global.isDarkModeEnabled"
                            class="btn size-9 rounded-full p-0 text-slate-500 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:text-navy-200 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 transition-colors"
                            aria-label="Toggle Dark Mode"
                            x-tooltip="'Toggle Dark Mode'">
                            {{-- Sun Icon for Dark Mode --}}
                            <svg x-show="$store.global.isDarkModeEnabled" xmlns="http://www.w3.org/2000/svg" class="size-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            {{-- Moon Icon for Light Mode --}}
                            <svg x-show="!$store.global.isDarkModeEnabled" xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-600 dark:text-navy-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

                        @auth
                            <a href="{{ route('dashboard') }}" class="btn h-9 rounded-full bg-primary px-4 text-xs font-semibold text-white shadow-md shadow-primary/30 hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus dark:shadow-accent/30 space-x-1.5">
                                <span>Dashboard</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn h-9 rounded-full px-3.5 text-xs font-semibold text-slate-700 hover:text-primary dark:text-navy-100 dark:hover:text-accent-light">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="btn h-9 rounded-full bg-primary px-4 text-xs font-semibold text-white shadow-md shadow-primary/30 hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus dark:shadow-accent/30 hidden sm:inline-flex space-x-1">
                                <span>Daftar Gratis</span>
                            </a>
                        @endauth
                    </div>
                </div>
            </header>

            {{-- Hero Full-Width Section (Edge-to-Edge) --}}
            <section class="w-full bg-white dark:bg-navy-800 border-b border-slate-200/80 dark:border-navy-700 py-16 sm:py-20 lg:py-24 transition-colors duration-300">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-12 gap-8 lg:gap-12 items-center">
                        <div class="col-span-12 lg:col-span-7 space-y-5 text-center lg:text-left">
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-800 dark:text-navy-50 leading-tight">
                                Compress, Convert & Olah File <span class="text-primary dark:text-accent-light">Online Cepat.</span>
                            </h1>

                            <p class="text-xs sm:text-sm lg:text-base text-slate-600 dark:text-navy-200 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                                Solusi lengkap kompresi foto, konversi format gambar, hingga pengubahan dokumen PDF ke Word secara instan langsung di browser Anda.
                            </p>

                            {{-- Lineone Search Bar (Redirects to /tools?q=...) --}}
                            <form action="{{ route('tools.index') }}" method="GET" class="pt-2 max-w-lg mx-auto lg:mx-0">
                                <div class="relative flex items-center">
                                    <input 
                                        name="q"
                                        type="text" 
                                        placeholder="Cari tool (contoh: Compress Gambar, PDF to Word)..."
                                        class="form-input h-11 w-full rounded-full border border-slate-300 bg-slate-50/80 px-4 pl-10 pr-24 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:text-navy-50 dark:placeholder:text-navy-400 dark:hover:border-navy-400 dark:focus:border-accent shadow-xs" />
                                    <span class="pointer-events-none absolute left-3.5 flex items-center justify-center text-slate-400 dark:text-navy-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </span>
                                    <button type="submit" class="absolute right-1.5 btn h-8 rounded-full bg-primary px-3.5 font-semibold text-white shadow-sm hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus text-xs">
                                        Cari
                                    </button>
                                </div>
                            </form>

                            {{-- Action Buttons --}}
                            <div class="pt-2 flex flex-wrap items-center justify-center lg:justify-start gap-3">
                                <a href="{{ route('tools.index') }}" class="btn rounded-full bg-primary px-6 py-2.5 text-xs font-bold text-white shadow-md shadow-primary/30 hover:bg-primary-focus dark:bg-accent dark:shadow-accent/30 dark:hover:bg-accent-focus space-x-2">
                                    <span>Jelajahi Semua Tools</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                                @guest
                                    <a href="{{ route('register') }}" class="btn rounded-full border border-slate-300 bg-white px-6 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100 dark:hover:bg-navy-600 shadow-xs">
                                        Daftar Gratis
                                    </a>
                                @endguest
                            </div>
                        </div>

                        {{-- Right Illustration (Magic Toolbox without background & without hover animation) --}}
                        <div class="col-span-12 lg:col-span-5 flex justify-center lg:justify-end">
                            <img class="w-80 sm:w-96 lg:w-[440px] max-w-full object-contain" src="{{ asset('images/illustrations/magic-toolbox-hero.png') }}" alt="Kotak Alat Digital Ajaib" />
                        </div>
                    </div>
                </div>
            </section>

            {{-- 4 Metric Cards Section --}}
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 sm:gap-5">
                    <div class="card p-4 sm:p-5 flex items-center space-x-3.5">
                        <div class="mask is-squircle flex size-11 shrink-0 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg sm:text-xl font-extrabold text-slate-800 dark:text-navy-100">{{ count($tools) }}+ Tools</p>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300 font-medium">Tersedia online</p>
                        </div>
                    </div>

                    <div class="card p-4 sm:p-5 flex items-center space-x-3.5">
                        <div class="mask is-squircle flex size-11 shrink-0 items-center justify-center bg-success/10 text-success">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg sm:text-xl font-extrabold text-slate-800 dark:text-navy-100">100% Aman</p>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300 font-medium">Privasi terenkripsi</p>
                        </div>
                    </div>

                    <div class="card p-4 sm:p-5 flex items-center space-x-3.5">
                        <div class="mask is-squircle flex size-11 shrink-0 items-center justify-center bg-info/10 text-info">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg sm:text-xl font-extrabold text-slate-800 dark:text-navy-100">Instan</p>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300 font-medium">Proses hitungan detik</p>
                        </div>
                    </div>

                    <div class="card p-4 sm:p-5 flex items-center space-x-3.5">
                        <div class="mask is-squircle flex size-11 shrink-0 items-center justify-center bg-warning/10 text-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg sm:text-xl font-extrabold text-slate-800 dark:text-navy-100">Akses 24/7</p>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300 font-medium">Tanpa instalasi</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Tools Directory Section (Lineone App Cards Grid) --}}
            <section id="tools-section" class="w-full bg-white dark:bg-navy-800/80 py-16 lg:py-20 border-y border-slate-200 dark:border-navy-700 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {{-- Section Header --}}
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8">
                        <div>
                            <p class="text-sm uppercase text-slate-400 dark:text-navy-300">Direktori Tools</p>
                            <h2 class="mt-1 text-xl font-semibold text-slate-600 dark:text-navy-100">
                                Katalog Alat Produktivitas
                            </h2>
                        </div>
                        {{-- Category Filter Pills --}}
                        <div class="mt-4 md:mt-0 flex flex-wrap gap-1.5 items-center">
                            <button 
                                @click="selectedCategory = 'all'"
                                :class="selectedCategory === 'all' ? 'bg-primary text-white dark:bg-accent' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80 dark:bg-navy-700 dark:text-navy-200 dark:hover:bg-navy-600'"
                                class="btn h-8 rounded-full px-3.5 text-xs font-semibold transition-all">
                                Semua Pilihan (<span x-text="tools.length"></span>)
                            </button>

                            <template x-for="cat in categories" :key="cat.name">
                                <button 
                                    @click="selectedCategory = cat.name"
                                    :class="selectedCategory.toLowerCase() === cat.name.toLowerCase() ? 'bg-primary text-white dark:bg-accent' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80 dark:bg-navy-700 dark:text-navy-200 dark:hover:bg-navy-600'"
                                    class="btn h-8 rounded-full px-3.5 text-xs font-semibold transition-all flex items-center space-x-1">
                                    <span x-text="cat.name"></span>
                                    <span class="opacity-70 text-[11px]">(<span x-text="cat.count"></span>)</span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Tools Grid (Exact Lineone layouts/onboarding-1 Style) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template x-for="tool in filteredTools" :key="tool.slug">
                            <div class="card flex flex-col justify-between h-full">
                                <div class="flex h-48 items-center justify-center p-5">
                                    <img class="max-h-40 max-w-full object-contain" :src="tool.image_url || '{{ asset('images/illustrations/creativedesign.svg') }}'" :alt="tool.name" />
                                </div>
                                <div class="flex flex-1 flex-col justify-between px-4 pb-8 text-center sm:px-5">
                                    <div>
                                        <div class="mb-2 flex flex-wrap items-center justify-center gap-1.5">
                                            <span class="badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-600 dark:text-navy-200 text-[11px] font-semibold px-2.5 py-0.5" x-text="tool.category"></span>
                                            <template x-if="tool.is_highlighted">
                                                <span class="badge rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 text-[10px] font-bold px-2 py-0.5 shadow-xs">🌟 Featured</span>
                                            </template>
                                            <template x-if="tool.is_maintenance">
                                                <span class="badge rounded-full bg-warning/15 text-warning text-[10px] font-bold px-2 py-0.5">Maintenance</span>
                                            </template>
                                            <template x-if="!tool.is_maintenance && tool.is_pro_only">
                                                <span class="badge rounded-full bg-linear-to-r from-amber-500 via-purple-600 to-indigo-600 text-white text-[10px] font-black px-2.5 py-0.5 shadow-xs uppercase">👑 PRO ONLY</span>
                                            </template>
                                            <template x-if="!tool.is_maintenance && !tool.is_pro_only && tool.badge === 'HOT'">
                                                <span class="badge rounded-full bg-linear-to-r from-red-500 to-orange-500 text-white text-[10px] font-black px-2.5 py-0.5 shadow-xs">🔥 HOT</span>
                                            </template>
                                            <template x-if="!tool.is_maintenance && !tool.is_pro_only && tool.badge === 'NEW'">
                                                <span class="badge rounded-full bg-linear-to-r from-emerald-500 to-teal-500 text-white text-[10px] font-black px-2.5 py-0.5 shadow-xs">✨ NEW</span>
                                            </template>
                                            <template x-if="!tool.is_maintenance && !tool.is_pro_only && tool.badge === 'PRO'">
                                                <span class="badge rounded-full bg-linear-to-r from-amber-500 to-purple-600 text-white text-[10px] font-black px-2.5 py-0.5 shadow-xs">👑 PRO</span>
                                            </template>
                                            <template x-if="!tool.is_maintenance && !tool.is_pro_only && tool.badge && !['HOT', 'NEW', 'PRO'].includes(tool.badge)">
                                                <span class="badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-500 dark:text-navy-200 text-[10px] font-bold px-2.5 py-0.5" x-text="tool.badge"></span>
                                            </template>
                                        </div>
                                        <h4 class="text-lg font-semibold text-slate-700 dark:text-navy-100" x-text="tool.name"></h4>
                                        <p class="pt-3 text-slate-500 dark:text-navy-300" x-text="tool.description"></p>
                                    </div>
                                    <div class="pt-8">
                                        <a :href="'/tool/' + tool.slug" class="btn bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                            Buka Tool
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Empty Search State --}}
                    <div x-show="filteredTools.length === 0" class="card py-12 px-6 text-center border border-slate-200 dark:border-navy-600">
                        <div class="mask is-squircle size-14 bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300 flex items-center justify-center mx-auto mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-slate-700 dark:text-navy-100">Tool Tidak Ditemukan</h4>
                        <p class="text-slate-400 dark:text-navy-300 text-xs mt-1 max-w-sm mx-auto">
                            Tidak ada tool yang cocok dengan kata kunci "<span class="font-bold text-slate-700 dark:text-navy-200" x-text="search"></span>".
                        </p>
                        <button @click="search = ''; selectedCategory = 'all'" class="btn rounded-full mt-4 bg-primary text-white dark:bg-accent h-8 px-4 text-xs font-semibold mx-auto">
                            Reset Pencarian
                        </button>
                    </div>

                    {{-- Bottom Explore All Tools Link --}}
                    <div class="mt-12 text-center">
                        <a href="{{ route('tools.index') }}" class="btn rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-navy-700 dark:hover:bg-navy-600 font-bold px-6 py-3 text-xs text-slate-700 dark:text-navy-100 shadow-xs inline-flex items-center space-x-2 transition-all">
                            <span>Jelajahi Seluruh {{ $totalAllTools ?? 3 }}+ Tools di Katalog</span>
                            <x-lucide-arrow-right class="size-4" />
                        </a>
                    </div>
                </div>
            </section>



            {{-- Why Choose Us / Features (Lineone layouts-onboarding-1 style cards) --}}
            <section id="features-section" class="w-full bg-slate-100/60 dark:bg-navy-800/60 py-16 lg:py-24 border-y border-slate-200 dark:border-navy-700 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="py-5 text-center lg:py-6">
                        <p class="text-sm uppercase text-slate-400 dark:text-navy-300">Keunggulan Utama</p>
                        <h2 class="mt-1 text-xl font-semibold text-slate-600 dark:text-navy-100">
                            Mengapa Memilih {{ config('app.name') }}?
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        {{-- Feature 1 --}}
                        <div class="card p-5">
                            <div class="mask is-squircle flex size-12 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-bold text-slate-700 dark:text-navy-100">
                                Kecepatan Maksimal
                            </h4>
                            <p class="pt-1.5 text-xs text-slate-400 dark:text-navy-300 leading-relaxed">
                                Ditenagai engine server modern untuk memproses kompresi dan konversi dalam sekejap.
                            </p>
                        </div>

                        {{-- Feature 2 --}}
                        <div class="card p-5">
                            <div class="mask is-squircle flex size-12 items-center justify-center bg-success/10 text-success mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-bold text-slate-700 dark:text-navy-100">
                                Privasi Terjamin
                            </h4>
                            <p class="pt-1.5 text-xs text-slate-400 dark:text-navy-300 leading-relaxed">
                                File diproses privat dan dihapus otomatis dari server setelah selesai diunduh.
                            </p>
                        </div>

                        {{-- Feature 3 --}}
                        <div class="card p-5">
                            <div class="mask is-squircle flex size-12 items-center justify-center bg-info/10 text-info mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-bold text-slate-700 dark:text-navy-100">
                                Lintas Perangkat
                            </h4>
                            <p class="pt-1.5 text-xs text-slate-400 dark:text-navy-300 leading-relaxed">
                                Berjalan lancar di browser komputer desktop, laptop, tablet, hingga smartphone.
                            </p>
                        </div>

                        {{-- Feature 4 --}}
                        <div class="card p-5">
                            <div class="mask is-squircle flex size-12 items-center justify-center bg-warning/10 text-warning mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-bold text-slate-700 dark:text-navy-100">
                                Kuota Fleksibel
                            </h4>
                            <p class="pt-1.5 text-xs text-slate-400 dark:text-navy-300 leading-relaxed">
                                Kuota gratis setiap hari atau upgrade ke Pro untuk akses pemrosesan tanpa batas.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Pricing Section (Exact from Lineone layouts/price-list-1) --}}
            <section id="pricing-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
                <div class="py-5 text-center lg:py-6 max-w-2xl mx-auto mb-6">
                    <p class="text-sm uppercase text-slate-400 dark:text-navy-300">Pilihan Paket</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-600 dark:text-navy-100">
                        Tingkatkan Produktivitas Anda Tanpa Batas
                    </h2>
                </div>

                <div class="grid max-w-4xl grid-cols-1 gap-4 sm:grid-cols-{{ min($plans->count(), 3) }} sm:gap-5 lg:gap-6 mx-auto items-stretch">
                    @forelse($plans as $plan)
                        @php
                            $isRecommended = $plan->price > 0 && ($plan->slug === 'pro' || $plan->slug === 'premium' || $loop->iteration === 2);
                            $breakdown = app(\App\Services\PriceCalculator::class)->breakdown($plan);
                            
                            // Dynamic Features Fallback
                            $planFeatures = $plan->features;
                            if (empty($planFeatures)) {
                                if ($plan->price == 0) {
                                    $planFeatures = [
                                        '5x / hari Kompres Gambar',
                                        '5x / hari Convert Gambar',
                                        '2x / hari PDF ke Word (Maks 5MB)',
                                        'Waktu Proses Standar'
                                    ];
                                } else {
                                    $planFeatures = [
                                        'Tanpa Batas Kuota Harian',
                                        'Buka Semua Fitur Preset Kustom',
                                        'Konversi PDF ke Word File Besar (50MB)',
                                        'Prioritas Server Kecepatan Tinggi'
                                    ];
                                }
                            }
                        @endphp
                        <div class="card p-4 text-center sm:p-5 relative flex flex-col justify-between h-full">
                            {{-- Top Badge for Recommended --}}
                            @if($isRecommended)
                                <div class="absolute top-0 right-0 p-3">
                                    <div class="badge rounded-full bg-info/10 text-info dark:bg-info/15 font-bold text-[10px] px-2.5 py-0.5">
                                        Recommended
                                    </div>
                                </div>
                            @endif

                            <div>
                                {{-- Icon (Price List 1 Style) --}}
                                <div class="mt-6">
                                    @if($plan->price == 0)
                                        <i class="fa fa-car text-5xl text-primary dark:text-accent-light"></i>
                                    @elseif($isRecommended)
                                        <i class="fa fa-plane text-5xl text-primary dark:text-accent-light"></i>
                                    @else
                                        <i class="fa fa-rocket text-5xl text-primary dark:text-accent-light"></i>
                                    @endif
                                </div>

                                {{-- Title & Subtitle --}}
                                <div class="mt-4">
                                    <h4 class="text-lg font-bold text-slate-700 dark:text-navy-100">
                                        {{ $plan->name }}
                                    </h4>
                                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5 min-h-[18px]">
                                        {{ $plan->description ?? 'Pilihan tepat untuk kebutuhan harian Anda' }}
                                    </p>
                                </div>

                                {{-- Price & Discount --}}
                                <div class="mt-4 min-h-[56px] flex flex-col justify-center">
                                    @if($breakdown['discount'] > 0)
                                        <div class="flex items-center justify-center space-x-1.5 mb-1">
                                            <span class="line-through text-slate-400 dark:text-navy-300 text-xs font-semibold">
                                                Rp {{ number_format($breakdown['basePrice'], 0, ',', '.') }}
                                            </span>
                                            <span class="badge rounded-full bg-success/15 text-success dark:bg-success/20 font-bold text-[10px] px-2 py-0.5">
                                                Hemat {{ $plan->discount_type === 'percent' ? $plan->discount_value . '%' : 'Rp ' . number_format($breakdown['discount'], 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endif
                                    <div>
                                        <span class="text-2xl sm:text-3xl tracking-tight font-extrabold text-primary dark:text-accent-light">
                                            Rp {{ number_format($breakdown['subtotal'], 0, ',', '.') }}
                                        </span>
                                        <span class="text-xs text-slate-400 dark:text-navy-300">
                                            /{{ $plan->duration_days ? $plan->duration_days . ' hari' : 'bulan' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Features List (Price List 1 Exact Style) --}}
                                <div class="mt-6 space-y-3 text-left">
                                    @foreach($planFeatures as $feature)
                                        <div class="flex items-start space-x-2.5">
                                            <div class="flex size-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light mt-0.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <span class="font-medium text-slate-600 dark:text-navy-100 text-xs">
                                                {{ $feature }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- CTA Button (Price List 1 Style) --}}
                            <div class="mt-6">
                                <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="btn rounded-full {{ $isRecommended ? 'bg-primary font-bold text-white shadow-md shadow-primary/30 hover:bg-primary-focus dark:bg-accent dark:shadow-accent/30 dark:hover:bg-accent-focus' : 'border border-slate-300 dark:border-navy-450 font-semibold text-slate-700 dark:text-navy-100 hover:bg-slate-100 dark:hover:bg-navy-600' }} w-full py-2.5 text-xs">
                                    Pilih Paket
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full card p-8 text-center text-slate-400 dark:text-navy-300 text-xs">
                            Belum ada data paket yang tersedia.
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- FAQ Section (Lineone Clean Accordion Style) --}}
            <section id="faq-section" class="w-full bg-slate-100/60 dark:bg-navy-800/60 py-16 lg:py-24 border-y border-slate-200 dark:border-navy-700 transition-colors">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="py-5 text-center lg:py-6 max-w-2xl mx-auto mb-6">
                        <p class="text-sm uppercase text-slate-400 dark:text-navy-300">Pertanyaan Umum</p>
                        <h2 class="mt-1 text-xl font-semibold text-slate-600 dark:text-navy-100">
                            Frequently Asked Questions
                        </h2>
                    </div>

                    <div class="space-y-3 text-xs">
                        {{-- FAQ 1 --}}
                        <div class="card overflow-hidden">
                            <button 
                                @click="faqOpen = (faqOpen === 1 ? null : 1)"
                                class="w-full px-5 py-4 flex items-center justify-between text-left font-bold text-slate-700 dark:text-navy-100 hover:text-primary dark:hover:text-accent-light transition-colors">
                                <span class="text-sm">Apakah file saya aman dan terjaga privasinya?</span>
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                     :class="faqOpen === 1 ? 'rotate-180 text-primary dark:text-accent-light' : 'text-slate-400 dark:text-navy-300'"
                                     class="size-4 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="faqOpen === 1" x-collapse>
                                <div class="px-5 pb-4 text-slate-500 dark:text-navy-300 leading-relaxed border-t border-slate-150 dark:border-navy-600 pt-3">
                                    Sangat aman. Semua proses kompresi dan konversi berlangsung secara otomatis melalui koneksi terenkripsi. File Anda tidak dibagikan ke pihak ketiga dan akan dihapus secara otomatis dari server kami.
                                </div>
                            </div>
                        </div>

                        {{-- FAQ 2 --}}
                        <div class="card overflow-hidden">
                            <button 
                                @click="faqOpen = (faqOpen === 2 ? null : 2)"
                                class="w-full px-5 py-4 flex items-center justify-between text-left font-bold text-slate-700 dark:text-navy-100 hover:text-primary dark:hover:text-accent-light transition-colors">
                                <span class="text-sm">Apakah layanan ini benar-benar bisa digunakan gratis?</span>
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                     :class="faqOpen === 2 ? 'rotate-180 text-primary dark:text-accent-light' : 'text-slate-400 dark:text-navy-300'"
                                     class="size-4 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="faqOpen === 2" x-collapse>
                                <div class="px-5 pb-4 text-slate-500 dark:text-navy-300 leading-relaxed border-t border-slate-150 dark:border-navy-600 pt-3">
                                    Ya! Anda dapat langsung menggunakan paket Gratis setiap hari untuk kebutuhan pemrosesan file standar. Jika memerlukan kuota tanpa batas dan pemrosesan file berukuran besar, Anda dapat beralih ke paket Pro.
                                </div>
                            </div>
                        </div>

                        {{-- FAQ 3 --}}
                        <div class="card overflow-hidden">
                            <button 
                                @click="faqOpen = (faqOpen === 3 ? null : 3)"
                                class="w-full px-5 py-4 flex items-center justify-between text-left font-bold text-slate-700 dark:text-navy-100 hover:text-primary dark:hover:text-accent-light transition-colors">
                                <span class="text-sm">Metode pembayaran apa saja yang didukung untuk paket Pro?</span>
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                     :class="faqOpen === 3 ? 'rotate-180 text-primary dark:text-accent-light' : 'text-slate-400 dark:text-navy-300'"
                                     class="size-4 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="faqOpen === 3" x-collapse>
                                <div class="px-5 pb-4 text-slate-500 dark:text-navy-300 leading-relaxed border-t border-slate-150 dark:border-navy-600 pt-3">
                                    Kami mendukung pembayaran otomatis melalui QRIS (GoPay, OVO, Dana, ShopeePay), Virtual Account Bank (BCA, Mandiri, BNI, BRI), serta Kartu Kredit melalui payment gateway Midtrans yang terverifikasi otomatis seketika.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- CTA Banner (Lineone style) --}}
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
                <div class="card relative overflow-hidden bg-primary dark:bg-accent p-8 sm:p-12 text-white text-center shadow-xl shadow-primary/20 dark:shadow-accent/20">
                    <div class="max-w-2xl mx-auto space-y-4">
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                            Mulai Olah Dokumen Anda Sekarang
                        </h2>
                        <p class="text-xs sm:text-sm text-white/80 max-w-lg mx-auto">
                            Nikmati kemudahan mengompres dan mengonversi berbagai format dokumen langsung dari browser tanpa batasan ribet.
                        </p>
                        <div class="pt-2 flex flex-wrap justify-center gap-3">
                            @guest
                                <a href="{{ route('register') }}" class="btn rounded-full bg-white text-primary hover:bg-slate-100 font-bold px-6 py-2.5 text-xs shadow-md">
                                    Daftar Akun Gratis
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="btn rounded-full bg-white text-primary hover:bg-slate-100 font-bold px-6 py-2.5 text-xs shadow-md">
                                    Buka Dashboard Saya
                                </a>
                            @endguest
                            <a href="#tools-section" class="btn rounded-full border border-white/40 text-white hover:bg-white/10 font-semibold px-6 py-2.5 text-xs">
                                Jelajahi Tools
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Footer (Lineone Clean Footer) --}}
            <footer class="w-full border-t border-slate-200 dark:border-navy-700 bg-white dark:bg-navy-900 py-10 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8 text-xs">
                        {{-- Col 1: Brand --}}
                        <div class="md:col-span-2 space-y-3">
                            <div class="flex items-center space-x-2.5">
                                @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                                    <div class="flex size-8 shrink-0 items-center justify-center">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($siteLogo) }}" class="size-full object-contain" alt="{{ $siteName }}" />
                                    </div>
                                @else
                                    <div class="mask is-squircle flex size-8 shrink-0 items-center justify-center bg-primary text-white dark:bg-accent shadow-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                @endif
                                <span class="text-base font-bold uppercase tracking-wider text-slate-800 dark:text-navy-50">{{ $siteName }}</span>
                            </div>
                            <p class="text-slate-400 dark:text-navy-300 max-w-sm leading-relaxed">
                                {{ $siteDesc }}
                            </p>
                        </div>

                        {{-- Col 2: Navigation --}}
                        <div>
                            <h4 class="font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300 mb-3">Navigasi</h4>
                            <ul class="space-y-2 text-slate-600 dark:text-navy-200">
                                <li><a href="#tools-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">Katalog Tools</a></li>
                                <li><a href="#features-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">Keunggulan</a></li>
                                <li><a href="#pricing-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">Paket & Harga</a></li>
                                <li><a href="#faq-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">FAQ</a></li>
                            </ul>
                        </div>

                        {{-- Col 3: Akun --}}
                        <div>
                            <h4 class="font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300 mb-3">Akun & Layanan</h4>
                            <ul class="space-y-2 text-slate-600 dark:text-navy-200">
                                @auth
                                    <li><a href="{{ route('dashboard') }}" class="hover:text-primary dark:hover:text-accent-light transition-colors">Dashboard Pengguna</a></li>
                                    <li><a href="{{ route('history') }}" class="hover:text-primary dark:hover:text-accent-light transition-colors">Riwayat Aktivitas</a></li>
                                    <li><a href="{{ route('profile') }}" class="hover:text-primary dark:hover:text-accent-light transition-colors">Pengaturan Profil</a></li>
                                @else
                                    <li><a href="{{ route('login') }}" class="hover:text-primary dark:hover:text-accent-light transition-colors">Masuk ke Akun</a></li>
                                    <li><a href="{{ route('register') }}" class="hover:text-primary dark:hover:text-accent-light transition-colors">Daftar Akun Baru</a></li>
                                @endauth
                            </ul>
                        </div>
                    </div>

                    <div class="border-t border-slate-150 dark:border-navy-700/80 pt-6 flex items-center justify-between text-xs text-slate-400 dark:text-navy-300">
                        <p>{{ $footerCopyright }}</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</x-base-layout>
