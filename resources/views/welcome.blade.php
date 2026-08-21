<x-base-layout is-header-blur="true" title="Compress, Convert, & Olah File Online Cepat">
    <div x-data="{ 
        search: '', 
        selectedCategory: 'all',
        faqOpen: null,
        tools: {{ json_encode($tools) }},
        get filteredTools() {
            return this.tools.filter(tool => {
                const matchSearch = tool.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                    tool.description.toLowerCase().includes(this.search.toLowerCase()) ||
                                    tool.category.toLowerCase().includes(this.search.toLowerCase());
                const matchCategory = this.selectedCategory === 'all' || tool.category.toLowerCase() === this.selectedCategory.toLowerCase();
                return matchSearch && matchCategory;
            });
        },
        get categories() {
            const list = [...new Set(this.tools.map(t => t.category))];
            return list;
        }
    }" class="w-full min-h-screen flex flex-col justify-between">

        {{-- Background Glow Decorators --}}
        <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-40 -left-40 size-96 rounded-full bg-primary/10 blur-3xl dark:bg-accent/10"></div>
            <div class="absolute top-1/3 -right-40 size-96 rounded-full bg-indigo-500/10 blur-3xl dark:bg-accent-light/10"></div>
            <div class="absolute -bottom-40 left-1/3 size-96 rounded-full bg-primary/5 blur-3xl dark:bg-accent/5"></div>
        </div>

        <div class="relative z-10 w-full">
            {{-- Navigation Header --}}
            <header class="sticky top-0 z-40 w-full border-b border-slate-200/80 bg-white/80 backdrop-blur-md dark:border-navy-700/80 dark:bg-navy-900/80 transition-colors duration-300">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
                    {{-- Brand Logo --}}
                    <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                        <div class="mask is-squircle flex size-10 items-center justify-center bg-gradient-to-tr from-primary to-primary-focus text-white shadow-md shadow-primary/30 transition-transform group-hover:scale-105 dark:from-accent dark:to-accent-focus dark:shadow-accent/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-lg font-bold tracking-tight text-slate-800 dark:text-navy-50 uppercase leading-none">
                                {{ config('app.name') }}
                            </span>
                            <span class="text-[10px] font-medium tracking-widest text-slate-400 dark:text-navy-300 uppercase">
                                Online Web Tools
                            </span>
                        </div>
                    </a>

                    {{-- Navigation Links (Desktop) --}}
                    <nav class="hidden md:flex items-center space-x-8 text-sm font-medium text-slate-600 dark:text-navy-200">
                        <a href="#tools-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">Semua Tools</a>
                        <a href="#how-it-works" class="hover:text-primary dark:hover:text-accent-light transition-colors">Cara Kerja</a>
                        <a href="#features-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">Keunggulan</a>
                        <a href="#pricing-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">Paket & Harga</a>
                        <a href="#faq-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">FAQ</a>
                    </nav>

                    {{-- Right Actions: Dark Mode & Auth --}}
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        {{-- Dark Mode Switcher --}}
                        <button 
                            @click="$store.global.isDarkModeEnabled = !$store.global.isDarkModeEnabled"
                            class="btn size-9 rounded-full p-0 text-slate-500 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:text-navy-200 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25 transition-colors"
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
                            <a href="{{ route('dashboard') }}" class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/30 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/30 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90 h-9 px-4 rounded-lg">
                                <span>Dashboard</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn font-medium text-slate-700 hover:text-primary dark:text-navy-100 dark:hover:text-accent-light px-3 text-sm">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="btn space-x-1.5 bg-primary font-medium text-white shadow-lg shadow-primary/30 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/30 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90 h-9 px-4 rounded-lg hidden sm:inline-flex text-sm">
                                <span>Daftar Gratis</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        @endauth
                    </div>
                </div>
            </header>

            {{-- Hero Section --}}
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-16 lg:pt-20 lg:pb-24 text-center">
                {{-- Announcement Pill Badge --}}
                <div class="inline-flex items-center space-x-2 rounded-full border border-primary/20 bg-primary/5 px-4 py-1.5 text-xs font-semibold text-primary dark:border-accent/20 dark:bg-accent/10 dark:text-accent-light mb-8 animate-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    <span>Platform Pengolah File Serbaguna & Cepat</span>
                </div>

                {{-- Main Headline --}}
                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-extrabold tracking-tight text-slate-800 dark:text-navy-50 leading-tight max-w-4xl mx-auto mb-6">
                    Compress, Convert, & Kelola File
                    <span class="bg-gradient-to-r from-primary via-indigo-500 to-accent bg-clip-text text-transparent dark:from-accent-light dark:via-indigo-400 dark:to-primary">Tanpa Ribet.</span>
                </h1>

                {{-- Subtitle --}}
                <p class="text-base sm:text-lg lg:text-xl text-slate-600 dark:text-navy-200 max-w-2xl mx-auto mb-10 leading-relaxed">
                    Satu tempat untuk segala kebutuhan kompresi gambar, konversi dokumen, dan pengolahan file instan langsung di browser dengan kualitas maksimal.
                </p>

                {{-- Hero Search Bar --}}
                <div class="max-w-xl mx-auto mb-10">
                    <div class="relative flex items-center shadow-lg shadow-slate-200/50 dark:shadow-navy-900/50 rounded-full">
                        <input 
                            x-model="search" 
                            type="text" 
                            placeholder="Cari tool (contoh: Compress Gambar, PDF to Word)..."
                            class="form-input h-14 w-full rounded-full border border-slate-300 bg-white px-5 py-3 pl-12 pr-28 text-sm placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-500 dark:bg-navy-800 dark:text-navy-50 dark:placeholder:text-navy-400 dark:hover:border-navy-400 dark:focus:border-accent transition-colors" />
                        <span class="pointer-events-none absolute left-4 flex items-center justify-center text-slate-400 dark:text-navy-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <a href="#tools-section" class="absolute right-2 btn h-10 rounded-full bg-primary px-4 font-medium text-white shadow-md hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus text-xs sm:text-sm">
                            Cari
                        </a>
                    </div>
                </div>

                {{-- Quick Action Buttons --}}
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <a href="#tools-section" class="btn space-x-2 bg-primary px-7 py-3 text-base font-semibold text-white shadow-lg shadow-primary/30 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/30 dark:hover:bg-accent-focus dark:focus:bg-accent-focus rounded-xl">
                        <span>Jelajahi Semua Tools</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="btn border border-slate-300 bg-white px-7 py-3 text-base font-semibold text-slate-700 hover:bg-slate-50 focus:bg-slate-50 active:bg-slate-100 dark:border-navy-500 dark:bg-navy-800 dark:text-navy-100 dark:hover:bg-navy-700 rounded-xl shadow-sm">
                            Daftar Akun Gratis
                        </a>
                    @endguest
                </div>

                {{-- Trust Stats / Badges --}}
                <div class="mt-16 grid grid-cols-2 gap-4 sm:grid-cols-4 sm:gap-6 max-w-4xl mx-auto">
                    <div class="card p-4 text-center border border-slate-150 dark:border-navy-700">
                        <div class="text-2xl sm:text-3xl font-bold text-primary dark:text-accent-light">{{ count($tools) }}+</div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-navy-300 font-medium">Tools Tersedia</div>
                    </div>
                    <div class="card p-4 text-center border border-slate-150 dark:border-navy-700">
                        <div class="text-2xl sm:text-3xl font-bold text-success">100%</div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-navy-300 font-medium">Aman & Privat</div>
                    </div>
                    <div class="card p-4 text-center border border-slate-150 dark:border-navy-700">
                        <div class="text-2xl sm:text-3xl font-bold text-info">Instant</div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-navy-300 font-medium">Pemrosesan Cepat</div>
                    </div>
                    <div class="card p-4 text-center border border-slate-150 dark:border-navy-700">
                        <div class="text-2xl sm:text-3xl font-bold text-warning">24/7</div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-navy-300 font-medium">Akses Kapan Saja</div>
                    </div>
                </div>
            </section>

            {{-- Tools Directory Section --}}
            <section id="tools-section" class="w-full bg-slate-100/70 dark:bg-navy-800/60 py-16 lg:py-24 border-y border-slate-200 dark:border-navy-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {{-- Section Header --}}
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
                        <div>
                            <div class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-semibold text-xs px-3 py-1 mb-2">
                                KATALOG TOOLS
                            </div>
                            <h2 class="text-3xl sm:text-4xl font-bold text-slate-800 dark:text-navy-50">
                                Pilih Tool yang Anda Butuhkan
                            </h2>
                            <p class="mt-2 text-slate-500 dark:text-navy-300 text-sm sm:text-base">
                                Klik tool di bawah untuk langsung mulai memproses file Anda.
                            </p>
                        </div>

                        {{-- Category Filter Pills --}}
                        <div class="mt-6 md:mt-0 flex flex-wrap gap-2">
                            <button 
                                @click="selectedCategory = 'all'"
                                :class="selectedCategory === 'all' ? 'bg-primary text-white dark:bg-accent' : 'bg-white text-slate-600 hover:bg-slate-200/60 dark:bg-navy-700 dark:text-navy-200 dark:hover:bg-navy-600'"
                                class="btn h-9 rounded-full px-4 text-xs font-semibold shadow-sm transition-all">
                                Semua (<span x-text="tools.length"></span>)
                            </button>
                            <template x-for="cat in categories" :key="cat">
                                <button 
                                    @click="selectedCategory = cat"
                                    :class="selectedCategory === cat ? 'bg-primary text-white dark:bg-accent' : 'bg-white text-slate-600 hover:bg-slate-200/60 dark:bg-navy-700 dark:text-navy-200 dark:hover:bg-navy-600'"
                                    class="btn h-9 rounded-full px-4 text-xs font-semibold shadow-sm transition-all"
                                    x-text="cat">
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Tools Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template x-for="tool in filteredTools" :key="tool.slug">
                            <a :href="'/tool/' + tool.slug" 
                               class="card group relative flex flex-col justify-between p-6 transition-all duration-300 hover:shadow-xl hover:shadow-primary/10 hover:-translate-y-1 hover:border-primary/50 dark:hover:border-accent/50 dark:hover:shadow-accent/10 border border-slate-200/80 dark:border-navy-600">
                                <div>
                                    <div class="flex items-center justify-between mb-5">
                                        {{-- Squircle Icon --}}
                                        <div class="mask is-squircle flex size-12 shrink-0 items-center justify-center bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-colors duration-300 dark:bg-accent-light/10 dark:text-accent-light dark:group-hover:bg-accent dark:group-hover:text-white shadow-sm">
                                            <template x-if="tool.icon === 'photo'">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </template>
                                            <template x-if="tool.icon === 'arrows-right-left'">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                </svg>
                                            </template>
                                            <template x-if="tool.icon === 'document-text'">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </template>
                                        </div>

                                        {{-- Category Badge --}}
                                        <span class="badge rounded-full bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-200 text-xs px-2.5 py-1 font-medium" x-text="tool.category"></span>
                                    </div>

                                    <h3 class="text-xl font-bold text-slate-800 dark:text-navy-50 group-hover:text-primary dark:group-hover:text-accent-light transition-colors mb-2" x-text="tool.name"></h3>
                                    <p class="text-sm text-slate-500 dark:text-navy-300 leading-relaxed" x-text="tool.description"></p>
                                </div>

                                <div class="mt-6 flex items-center justify-between border-t border-slate-150 dark:border-navy-600/70 pt-4 text-sm font-semibold text-primary dark:text-accent-light">
                                    <span>Gunakan Tool</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5 transition-transform duration-300 group-hover:translate-x-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </div>
                            </a>
                        </template>
                    </div>

                    {{-- Empty State When Search Not Found --}}
                    <div x-show="filteredTools.length === 0" class="card py-16 px-6 text-center border border-slate-200 dark:border-navy-600">
                        <div class="mask is-squircle size-16 bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300 flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h4 class="text-xl font-semibold text-slate-700 dark:text-navy-100">Tool Tidak Ditemukan</h4>
                        <p class="text-slate-500 dark:text-navy-300 text-sm mt-1 max-w-sm mx-auto">
                            Tidak ada tool yang cocok dengan kata kunci "<span class="font-medium text-slate-700 dark:text-navy-200" x-text="search"></span>".
                        </p>
                        <button @click="search = ''; selectedCategory = 'all'" class="btn mt-4 bg-primary text-white dark:bg-accent h-9 px-4 rounded-lg text-xs font-semibold mx-auto">
                            Reset Pencarian
                        </button>
                    </div>
                </div>
            </section>

            {{-- How It Works Section --}}
            <section id="how-it-works" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <div class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-semibold text-xs px-3 py-1 mb-3">
                        ALUR KERJA
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold text-slate-800 dark:text-navy-50">
                        Semudah 3 Langkah Cepat
                    </h2>
                    <p class="text-slate-500 dark:text-navy-300 mt-3 text-base">
                        Tidak perlu menginstal aplikasi tambahan di komputer atau smartphone Anda.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                    {{-- Step 1 --}}
                    <div class="card p-8 text-center relative border border-slate-200 dark:border-navy-600 transition-all hover:shadow-lg">
                        <div class="mask is-squircle size-16 bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light flex items-center justify-center mx-auto mb-6 text-2xl font-black">
                            1
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-navy-50 mb-3">Upload File</h3>
                        <p class="text-slate-500 dark:text-navy-300 text-sm leading-relaxed">
                            Pilih atau drag & drop file yang ingin Anda proses langsung ke dalam kotak upload yang tersedia.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="card p-8 text-center relative border border-slate-200 dark:border-navy-600 transition-all hover:shadow-lg">
                        <div class="mask is-squircle size-16 bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light flex items-center justify-center mx-auto mb-6 text-2xl font-black">
                            2
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-navy-50 mb-3">Pilih Pengaturan</h3>
                        <p class="text-slate-500 dark:text-navy-300 text-sm leading-relaxed">
                            Tentukan format keluaran, rasio kompresi, atau opsi khusus sesuai kebutuhan pekerjaan Anda.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="card p-8 text-center relative border border-slate-200 dark:border-navy-600 transition-all hover:shadow-lg">
                        <div class="mask is-squircle size-16 bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light flex items-center justify-center mx-auto mb-6 text-2xl font-black">
                            3
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-navy-50 mb-3">Proses & Unduh</h3>
                        <p class="text-slate-500 dark:text-navy-300 text-sm leading-relaxed">
                            Sistem secara instan mengolah file Anda. Klik tombol unduh dan file siap langsung digunakan.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Why Choose Us / Features --}}
            <section id="features-section" class="w-full bg-slate-100/70 dark:bg-navy-800/60 py-20 lg:py-28 border-y border-slate-200 dark:border-navy-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-2xl mx-auto mb-16">
                        <div class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-semibold text-xs px-3 py-1 mb-3">
                            KEUNGGULAN UTAMA
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-slate-800 dark:text-navy-50">
                            Mengapa Menggunakan {{ config('app.name') }}?
                        </h2>
                        <p class="text-slate-500 dark:text-navy-300 mt-3 text-base">
                            Dirancang untuk kenyamanan, kecepatan, dan keamanan pemrosesan data Anda.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="card p-6 border border-slate-200 dark:border-navy-600">
                            <div class="mask is-squircle size-12 bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light flex items-center justify-center mb-5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-slate-800 dark:text-navy-50 mb-2">Kecepatan Tinggi</h4>
                            <p class="text-sm text-slate-500 dark:text-navy-300 leading-relaxed">
                                Ditenagai engine pemrosesan server modern untuk hasil konversi dan kompresi tanpa menunggu lama.
                            </p>
                        </div>

                        <div class="card p-6 border border-slate-200 dark:border-navy-600">
                            <div class="mask is-squircle size-12 bg-success/10 text-success flex items-center justify-center mb-5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-slate-800 dark:text-navy-50 mb-2">Privasi Terjamin</h4>
                            <p class="text-sm text-slate-500 dark:text-navy-300 leading-relaxed">
                                File Anda bersifat privat dan dihapus secara otomatis dari sistem setelah proses selesai.
                            </p>
                        </div>

                        <div class="card p-6 border border-slate-200 dark:border-navy-600">
                            <div class="mask is-squircle size-12 bg-info/10 text-info flex items-center justify-center mb-5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-slate-800 dark:text-navy-50 mb-2">Akses Lintas Perangkat</h4>
                            <p class="text-sm text-slate-500 dark:text-navy-300 leading-relaxed">
                                Bekerja secara responsif dan lancar di browser komputer, tablet, maupun smartphone Anda.
                            </p>
                        </div>

                        <div class="card p-6 border border-slate-200 dark:border-navy-600">
                            <div class="mask is-squircle size-12 bg-warning/10 text-warning flex items-center justify-center mb-5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-slate-800 dark:text-navy-50 mb-2">Paket Fleksibel</h4>
                            <p class="text-sm text-slate-500 dark:text-navy-300 leading-relaxed">
                                Gunakan secara gratis setiap hari atau upgrade ke Pro untuk akses tanpa batas dan ukuran file besar.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Pricing Section (Exact from layouts/price-list-1) --}}
            <section id="pricing-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
                <div class="py-5 text-center lg:py-6 max-w-2xl mx-auto mb-10">
                    <p class="text-sm uppercase tracking-wider text-slate-500 dark:text-navy-300">PILIH PAKET ANDA</p>
                    <h3 class="mt-1 text-xl font-semibold text-slate-600 dark:text-navy-100 lg:text-2xl">
                        Tingkatkan Produktivitas Anda Tanpa Batas
                    </h3>
                </div>

                <div class="grid max-w-4xl grid-cols-1 gap-4 sm:grid-cols-{{ min($plans->count(), 3) }} sm:gap-5 lg:gap-6 mx-auto items-stretch">
                    @forelse($plans as $plan)
                        @php
                            $isRecommended = $plan->price > 0 && ($plan->slug === 'pro' || $plan->slug === 'premium' || $loop->iteration === 2);
                        @endphp
                        <div class="card p-4 text-center sm:p-5 relative flex flex-col justify-between h-full">
                            {{-- Top Badge for Recommended --}}
                            @if($isRecommended)
                                <div class="absolute top-0 right-0 p-3">
                                    <div class="badge rounded-full bg-info/10 text-info dark:bg-info/15 font-semibold text-xs px-3 py-1">
                                        Recommended
                                    </div>
                                </div>
                            @endif

                            <div>
                                {{-- Icon (Price List 1 Style) --}}
                                <div class="mt-8">
                                    @if($plan->price == 0)
                                        <i class="fa fa-car text-6xl text-primary dark:text-accent-light"></i>
                                    @elseif($isRecommended)
                                        <i class="fa fa-plane text-6xl text-primary dark:text-accent-light"></i>
                                    @else
                                        <i class="fa fa-rocket text-6xl text-primary dark:text-accent-light"></i>
                                    @endif
                                </div>

                                {{-- Title & Subtitle --}}
                                <div class="mt-5">
                                    <h4 class="text-xl font-semibold text-slate-600 dark:text-navy-100">
                                        {{ $plan->name }}
                                    </h4>
                                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-1 min-h-[20px]">
                                        {{ $plan->description ?? 'Pilihan tepat untuk kebutuhan harian Anda' }}
                                    </p>
                                </div>

                                {{-- Price --}}
                                <div class="mt-5">
                                    <span class="text-3xl sm:text-4xl tracking-tight font-bold text-primary dark:text-accent-light">
                                        Rp {{ number_format($plan->price, 0, ',', '.') }}
                                    </span>
                                    <span class="text-xs text-slate-500 dark:text-navy-300">
                                        /{{ $plan->duration_days ? $plan->duration_days . ' hari' : 'bulan' }}
                                    </span>
                                </div>

                                {{-- Features List (Price List 1 Exact Style) --}}
                                <div class="mt-8 space-y-4 text-left">
                                    @foreach($plan->features ?? [] as $feature)
                                        <div class="flex items-start space-x-3">
                                            <div class="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <span class="font-medium text-slate-700 dark:text-navy-100 text-sm">
                                                {{ $feature }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- CTA Button (Price List 1 Style) --}}
                            <div class="mt-8">
                                @auth
                                    <a href="{{ route('pricing') }}" class="btn rounded-full {{ $isRecommended ? 'bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90' : 'border border-slate-200 font-medium text-primary hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-500 dark:text-accent-light dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90' }} w-full text-xs py-2.5">
                                        {{ $plan->price == 0 ? 'Paket Aktif' : 'Pilih Paket' }}
                                    </a>
                                @else
                                    <a href="{{ route('register') }}" class="btn rounded-full {{ $isRecommended ? 'bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90' : 'border border-slate-200 font-medium text-primary hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-500 dark:text-accent-light dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90' }} w-full text-xs py-2.5">
                                        {{ $plan->price == 0 ? 'Daftar Gratis' : 'Pilih Paket' }}
                                    </a>
                                @endauth
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full card p-8 text-center text-slate-500">
                            Belum ada data paket yang tersedia.
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- FAQ Section --}}
            <section id="faq-section" class="w-full bg-slate-100/70 dark:bg-navy-800/60 py-20 lg:py-28 border-y border-slate-200 dark:border-navy-700">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-2xl mx-auto mb-16">
                        <div class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-semibold text-xs px-3 py-1 mb-3">
                            PERTANYAAN UMUM
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-slate-800 dark:text-navy-50">
                            Frequently Asked Questions
                        </h2>
                        <p class="text-slate-500 dark:text-navy-300 mt-3 text-base">
                            Pertanyaan yang sering diajukan mengenai penggunaan layanan {{ config('app.name') }}.
                        </p>
                    </div>

                    <div class="space-y-4">
                        {{-- FAQ 1 --}}
                        <div class="card border border-slate-200 dark:border-navy-600 overflow-hidden">
                            <button 
                                @click="faqOpen = (faqOpen === 1 ? null : 1)"
                                class="w-full px-6 py-4.5 flex items-center justify-between text-left font-semibold text-slate-800 dark:text-navy-100 hover:text-primary dark:hover:text-accent-light transition-colors">
                                <span class="text-base">Apakah file saya aman dan terjaga privasinya?</span>
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                     :class="faqOpen === 1 ? 'rotate-180 text-primary dark:text-accent-light' : 'text-slate-400 dark:text-navy-300'"
                                     class="size-5 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="faqOpen === 1" x-collapse>
                                <div class="px-6 pb-5 text-sm text-slate-500 dark:text-navy-300 leading-relaxed border-t border-slate-100 dark:border-navy-600/60 pt-3">
                                    Sangat aman. Semua proses kompresi dan konversi berlangsung secara otomatis melalui koneksi terenkripsi SSL. File Anda tidak dibagikan ke pihak ketiga dan akan dihapus secara otomatis dari server kami.
                                </div>
                            </div>
                        </div>

                        {{-- FAQ 2 --}}
                        <div class="card border border-slate-200 dark:border-navy-600 overflow-hidden">
                            <button 
                                @click="faqOpen = (faqOpen === 2 ? null : 2)"
                                class="w-full px-6 py-4.5 flex items-center justify-between text-left font-semibold text-slate-800 dark:text-navy-100 hover:text-primary dark:hover:text-accent-light transition-colors">
                                <span class="text-base">Apakah layanan ini benar-benar bisa digunakan gratis?</span>
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                     :class="faqOpen === 2 ? 'rotate-180 text-primary dark:text-accent-light' : 'text-slate-400 dark:text-navy-300'"
                                     class="size-5 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="faqOpen === 2" x-collapse>
                                <div class="px-6 pb-5 text-sm text-slate-500 dark:text-navy-300 leading-relaxed border-t border-slate-100 dark:border-navy-600/60 pt-3">
                                    Ya! Anda dapat langsung menggunakan paket Gratis setiap hari untuk kebutuhan pemrosesan file sehari-hari. Jika membutuhkan pemrosesan tanpa batas dan file besar, Anda dapat beralih ke paket Pro kapan pun.
                                </div>
                            </div>
                        </div>

                        {{-- FAQ 3 --}}
                        <div class="card border border-slate-200 dark:border-navy-600 overflow-hidden">
                            <button 
                                @click="faqOpen = (faqOpen === 3 ? null : 3)"
                                class="w-full px-6 py-4.5 flex items-center justify-between text-left font-semibold text-slate-800 dark:text-navy-100 hover:text-primary dark:hover:text-accent-light transition-colors">
                                <span class="text-base">Metode pembayaran apa saja yang didukung untuk upgrade Pro?</span>
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                     :class="faqOpen === 3 ? 'rotate-180 text-primary dark:text-accent-light' : 'text-slate-400 dark:text-navy-300'"
                                     class="size-5 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="faqOpen === 3" x-collapse>
                                <div class="px-6 pb-5 text-sm text-slate-500 dark:text-navy-300 leading-relaxed border-t border-slate-100 dark:border-navy-600/60 pt-3">
                                    Kami mendukung pembayaran otomatis melalui QRIS (GoPay, OVO, Dana, ShopeePay), Virtual Account Bank (BCA, Mandiri, BNI, BRI), serta Kartu Kredit melalui payment gateway Midtrans.
                                </div>
                            </div>
                        </div>

                        {{-- FAQ 4 --}}
                        <div class="card border border-slate-200 dark:border-navy-600 overflow-hidden">
                            <button 
                                @click="faqOpen = (faqOpen === 4 ? null : 4)"
                                class="w-full px-6 py-4.5 flex items-center justify-between text-left font-semibold text-slate-800 dark:text-navy-100 hover:text-primary dark:hover:text-accent-light transition-colors">
                                <span class="text-base">Apakah kualitas file saya akan berkurang setelah dikompres?</span>
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                     :class="faqOpen === 4 ? 'rotate-180 text-primary dark:text-accent-light' : 'text-slate-400 dark:text-navy-300'"
                                     class="size-5 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="faqOpen === 4" x-collapse>
                                <div class="px-6 pb-5 text-sm text-slate-500 dark:text-navy-300 leading-relaxed border-t border-slate-100 dark:border-navy-600/60 pt-3">
                                    Tool kompresi kami menggunakan algoritma *smart compression* yang memangkas ukuran byte file secara drastis tanpa menurunkan kejernihan visual secara kasat mata.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- CTA Banner --}}
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-24">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-primary to-primary-focus dark:from-accent dark:to-accent-focus p-8 sm:p-12 lg:p-16 text-white text-center shadow-2xl shadow-primary/20">
                    <div class="relative z-10 max-w-3xl mx-auto">
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight mb-4">
                            Siap Menghemat Waktu Pekerjaan Anda?
                        </h2>
                        <p class="text-base sm:text-lg text-white/85 mb-8 max-w-xl mx-auto">
                            Gabung sekarang dan nikmati kemudahan mengolah berbagai format file langsung dari satu tempat tanpa batasan.
                        </p>
                        <div class="flex flex-wrap justify-center gap-4">
                            @guest
                                <a href="{{ route('register') }}" class="btn bg-white text-primary hover:bg-slate-100 dark:text-navy-900 font-bold px-8 py-3.5 rounded-xl shadow-lg text-base">
                                    Daftar Akun Sekarang
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="btn bg-white text-primary hover:bg-slate-100 dark:text-navy-900 font-bold px-8 py-3.5 rounded-xl shadow-lg text-base">
                                    Buka Dashboard Saya
                                </a>
                            @endguest
                            <a href="#tools-section" class="btn border border-white/40 text-white hover:bg-white/10 font-semibold px-8 py-3.5 rounded-xl text-base">
                                Coba Tools
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Footer --}}
            <footer class="w-full border-t border-slate-200 dark:border-navy-700 bg-white dark:bg-navy-900 py-12 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                        {{-- Col 1: Brand --}}
                        <div class="md:col-span-2 space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="mask is-squircle flex size-9 items-center justify-center bg-primary text-white dark:bg-accent shadow-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <span class="text-xl font-bold uppercase tracking-wider text-slate-800 dark:text-navy-50">{{ config('app.name') }}</span>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-navy-300 max-w-sm leading-relaxed">
                                Solusi perkakas digital instan untuk mengolah, mengompres, dan mengonversi file Anda setiap hari tanpa instalasi software.
                            </p>
                        </div>

                        {{-- Col 2: Navigation --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300 mb-4">Navigasi</h4>
                            <ul class="space-y-2 text-sm text-slate-600 dark:text-navy-200">
                                <li><a href="#tools-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">Katalog Tools</a></li>
                                <li><a href="#how-it-works" class="hover:text-primary dark:hover:text-accent-light transition-colors">Cara Kerja</a></li>
                                <li><a href="#pricing-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">Paket Berlangganan</a></li>
                                <li><a href="#faq-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">FAQ</a></li>
                            </ul>
                        </div>

                        {{-- Col 3: Akun --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300 mb-4">Akun & Layanan</h4>
                            <ul class="space-y-2 text-sm text-slate-600 dark:text-navy-200">
                                @auth
                                    <li><a href="{{ route('dashboard') }}" class="hover:text-primary dark:hover:text-accent-light transition-colors">Dashboard Pengguna</a></li>
                                    <li><a href="{{ route('history') }}" class="hover:text-primary dark:hover:text-accent-light transition-colors">Riwayat Aktivitas</a></li>
                                    <li><a href="{{ route('profile') }}" class="hover:text-primary dark:hover:text-accent-light transition-colors">Pengaturan Profil</a></li>
                                @else
                                    <li><a href="{{ route('login') }}" class="hover:text-primary dark:hover:text-accent-light transition-colors">Masuk ke Akun</a></li>
                                    <li><a href="{{ route('register') }}" class="hover:text-primary dark:hover:text-accent-light transition-colors">Registrasi Akun Baru</a></li>
                                @endauth
                            </ul>
                        </div>
                    </div>

                    <div class="border-t border-slate-150 dark:border-navy-700/80 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400 dark:text-navy-300">
                        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                        <div class="flex items-center space-x-6">
                            <span>Dirancang dengan Theme Lineone</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</x-base-layout>
