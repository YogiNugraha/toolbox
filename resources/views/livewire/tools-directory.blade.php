<div x-data="{
    showAuthModal: false,
    selectedToolName: '',
    selectedToolSlug: '',
    selectedToolCategory: '',
    selectedToolIsPro: false,
    openAuthGate(name, slug, category, isPro) {
        this.selectedToolName = name;
        this.selectedToolSlug = slug;
        this.selectedToolCategory = category;
        this.selectedToolIsPro = isPro;
        this.showAuthModal = true;
    }
}" class="w-full min-h-screen flex flex-col justify-between bg-slate-50 dark:bg-navy-900 transition-colors duration-300">

    @section('title', 'Katalog & Direktori Seluruh Tools - ' . $siteName)

    <div class="relative w-full">
        {{-- Navigation Header (Sticky Blur Header) --}}
        <header class="sticky top-0 z-40 w-full border-b border-slate-200/80 bg-white/90 backdrop-blur-md dark:border-navy-700/80 dark:bg-navy-900/90 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
                @php
                    $siteLogo = \App\Models\Setting::get('site_logo');
                    $footerCopyright = \App\Models\Setting::get('footer_copyright', '© ' . date('Y') . ' ' . $siteName . '. All rights reserved.');
                @endphp

                {{-- Brand Logo --}}
                <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                    @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                        <div class="flex size-10 shrink-0 items-center justify-center">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($siteLogo) }}" class="size-full object-contain" alt="{{ $siteName }}" />
                        </div>
                    @else
                        <div class="mask is-squircle flex size-10 shrink-0 items-center justify-center bg-primary text-white shadow-xs dark:bg-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <span class="text-base sm:text-lg font-medium tracking-wide text-slate-800 dark:text-navy-50 uppercase leading-none">
                            {{ $siteName }}
                        </span>
                        <span class="text-xs font-medium tracking-wider text-slate-400 dark:text-navy-300 uppercase mt-0.5">
                            {{ $siteTagline }}
                        </span>
                    </div>
                </a>

                {{-- Navigation Links (Desktop) --}}
                <nav class="hidden md:flex items-center space-x-6 lg:space-x-8 text-xs font-medium text-slate-600 dark:text-navy-200">
                    <a href="{{ route('home') }}" class="hover:text-primary dark:hover:text-accent-light transition-colors">Beranda</a>
                    <a href="{{ route('tools.index') }}" class="text-primary dark:text-accent-light font-semibold">Katalog Tools</a>
                    <a href="{{ route('home') }}#features-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">Keunggulan</a>
                    <a href="{{ route('home') }}#pricing-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">Paket & Harga</a>
                    <a href="{{ route('home') }}#faq-section" class="hover:text-primary dark:hover:text-accent-light transition-colors">FAQ</a>
                </nav>

                {{-- Right Actions: Auth Buttons --}}
                <div class="flex items-center space-x-3 sm:space-x-4">

                    @auth
                        <a href="{{ route('dashboard') }}" class="btn h-9 rounded-full bg-primary px-4 text-xs font-medium text-white shadow-xs hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus space-x-1.5">
                            <span>Dashboard</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn h-9 rounded-full px-3.5 text-xs font-medium text-slate-700 hover:text-primary dark:text-navy-100 dark:hover:text-accent-light">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="btn h-9 rounded-full bg-primary px-4 text-xs font-medium text-white shadow-xs hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus hidden sm:inline-flex space-x-1">
                            <span>Daftar Gratis</span>
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Hero Header & Search Section --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-10 sm:pt-12 sm:pb-12 text-center">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-wide text-slate-800 dark:text-navy-50 leading-tight max-w-3xl mx-auto">
                Katalog Seluruh <span class="text-primary dark:text-accent-light">Tools Produktivitas</span>
            </h1>

            <p class="mt-3 text-xs sm:text-sm lg:text-base text-slate-500 dark:text-navy-300 max-w-2xl mx-auto leading-relaxed">
                Temukan dan gunakan ragam alat pengolah dokumen, konversi format gambar, kompresi, dan utilitas digital instan langsung di browser Anda.
            </p>

            {{-- Interactive Search Input --}}
            <div class="mt-6 max-w-lg mx-auto">
                <div class="relative flex items-center rounded-full bg-white dark:bg-navy-800 border border-slate-300 dark:border-navy-600 p-1">
                    <span class="pointer-events-none pl-3 text-slate-400 dark:text-navy-300">
                        <x-lucide-search class="size-4" />
                    </span>
                    <input 
                        wire:model.live.debounce.250ms="search" 
                        type="text" 
                        placeholder="Cari tool (contoh: Compress Gambar, PDF to Word, WebP)..."
                        class="form-input h-10 w-full border-none bg-transparent px-3 text-xs placeholder:text-slate-400 focus:outline-hidden dark:text-navy-50 dark:placeholder:text-navy-400" 
                    />
                    @if($search)
                        <button 
                            wire:click="$set('search', '')" 
                            class="btn size-8 rounded-full p-0 text-slate-400 hover:text-slate-700 dark:text-navy-300 dark:hover:text-navy-100 mr-1"
                            title="Hapus pencarian">
                            <x-lucide-x class="size-4" />
                        </button>
                    @endif
                </div>
            </div>

            {{-- Category Filter Pills --}}
            <div class="mt-6 flex flex-wrap gap-2 items-center justify-center">
                <button 
                    wire:click="selectCategory('all')"
                    class="btn h-8 rounded-full px-3.5 text-xs font-medium transition-all {{ $category === 'all' ? 'bg-primary text-white dark:bg-accent' : 'bg-slate-150 text-slate-700 hover:bg-slate-200 dark:bg-navy-600 dark:text-navy-200 dark:hover:bg-navy-500' }}">
                    <span>Semua Pilihan</span>
                    <span class="ml-1 opacity-75">({{ $totalAllTools }})</span>
                </button>

                @foreach($categories as $cat)
                    <button 
                        wire:click="selectCategory('{{ $cat['name'] }}')"
                        class="btn h-8 rounded-full px-3.5 text-xs font-medium transition-all {{ strtolower($category) === strtolower($cat['name']) ? 'bg-primary text-white dark:bg-accent' : 'bg-slate-150 text-slate-700 hover:bg-slate-200 dark:bg-navy-600 dark:text-navy-200 dark:hover:bg-navy-500' }}">
                        <span>{{ $cat['name'] }}</span>
                        <span class="ml-1 opacity-75">({{ $cat['count'] }})</span>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- Main Tools Content Section --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
            @if($filteredTools->isEmpty())
                {{-- Empty Search Results State --}}
                <div class="card py-16 px-6 text-center border border-slate-150 dark:border-navy-600 max-w-lg mx-auto">
                    <div class="mask is-squircle size-14 bg-slate-100 text-slate-400 dark:bg-navy-600 dark:text-navy-300 flex items-center justify-center mx-auto mb-4">
                        <x-lucide-search class="size-7" />
                    </div>
                    <h3 class="text-base font-medium tracking-wide text-slate-700 dark:text-navy-100">Tool Tidak Ditemukan</h3>
                    <p class="text-slate-500 dark:text-navy-300 text-xs mt-1.5 max-w-sm mx-auto leading-relaxed">
                        Tidak ada alat yang cocok dengan kata kunci atau filter "<span class="font-medium text-slate-700 dark:text-navy-100">{{ $search ?: $category }}</span>".
                    </p>
                    <div class="mt-5">
                        <button 
                            wire:click="resetFilters" 
                            class="btn h-8 rounded-full bg-primary text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus px-4 text-xs font-medium">
                            Reset Pencarian & Tampilkan Semua
                        </button>
                    </div>
                </div>
            @else
                {{-- Grouped by Category Section --}}
                <div class="space-y-12">
                    @foreach($groupedTools as $categoryName => $toolsList)
                        <div class="space-y-5">
                            {{-- Category Title Header --}}
                            <div class="flex items-center justify-between border-b border-slate-200 dark:border-navy-600 pb-3">
                                <div class="flex items-center space-x-2.5">
                                    <div class="mask is-squircle flex size-8 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                        @if(strtolower($categoryName) === 'image')
                                            <x-lucide-image class="size-4.5" />
                                        @elseif(strtolower($categoryName) === 'document')
                                            <x-lucide-file-text class="size-4.5" />
                                        @elseif(strtolower($categoryName) === 'audio')
                                            <x-lucide-music class="size-4.5" />
                                        @elseif(strtolower($categoryName) === 'video')
                                            <x-lucide-video class="size-4.5" />
                                        @else
                                            <x-lucide-wrench class="size-4.5" />
                                        @endif
                                    </div>
                                    <div>
                                        <h2 class="text-base sm:text-lg font-medium tracking-wide text-slate-800 dark:text-navy-50">
                                            Kategori {{ $categoryName }}
                                        </h2>
                                    </div>
                                </div>
                                <span class="badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-500 dark:text-navy-100 text-xs font-medium px-2.5 py-0.5">
                                    {{ $toolsList->count() }} Tools
                                </span>
                            </div>

                            {{-- Tools Grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($toolsList as $tool)
                                    <div class="card flex flex-col justify-between h-full group">
                                        {{-- Illustration Image --}}
                                        <div class="flex h-48 items-center justify-center p-5 bg-slate-50 dark:bg-navy-800 rounded-t-lg">
                                            <img class="max-h-40 max-w-full object-contain transition-transform duration-300 group-hover:scale-105" src="{{ $tool->image_url ?: asset('images/illustrations/creativedesign.svg') }}" alt="{{ $tool->name }}" />
                                        </div>

                                        {{-- Card Body --}}
                                        <div class="flex flex-1 flex-col justify-between p-4 sm:p-5 text-center">
                                            <div>
                                                {{-- Badges --}}
                                                <div class="mb-2.5 flex flex-wrap items-center justify-center gap-1.5">
                                                    <span class="badge rounded-full bg-slate-150 text-slate-800 dark:bg-navy-500 dark:text-navy-100 text-xs font-medium px-2.5 py-0.5">
                                                        {{ $tool->category }}
                                                    </span>

                                                    @if($tool->is_highlighted)
                                                        <span class="badge rounded-full bg-warning/10 text-warning dark:bg-warning/15 text-xs font-medium px-2 py-0.5">
                                                            Featured
                                                        </span>
                                                    @endif

                                                    @if($tool->is_maintenance)
                                                        <span class="badge rounded-full bg-warning/10 text-warning dark:bg-warning/15 text-xs font-medium px-2 py-0.5">
                                                            Maintenance
                                                        </span>
                                                    @elseif($tool->is_pro_only)
                                                        <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light text-xs font-medium px-2 py-0.5 uppercase">
                                                            PRO ONLY
                                                        </span>
                                                    @elseif($tool->badge === 'HOT')
                                                        <span class="badge rounded-full bg-error/10 text-error dark:bg-error/15 text-xs font-medium px-2 py-0.5 uppercase">
                                                            HOT
                                                        </span>
                                                    @elseif($tool->badge === 'NEW')
                                                        <span class="badge rounded-full bg-success/10 text-success dark:bg-success/15 text-xs font-medium px-2 py-0.5 uppercase">
                                                            NEW
                                                        </span>
                                                    @elseif($tool->badge === 'PRO')
                                                        <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light text-xs font-medium px-2 py-0.5 uppercase">
                                                            PRO
                                                        </span>
                                                    @elseif($tool->badge)
                                                        <span class="badge rounded-full bg-info/10 text-info dark:bg-info/15 text-xs font-medium px-2 py-0.5 uppercase">
                                                            {{ $tool->badge }}
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Title & Description --}}
                                                <h3 class="text-base font-medium tracking-wide text-slate-700 dark:text-navy-100 line-clamp-1">
                                                    {{ $tool->name }}
                                                </h3>
                                                <p class="pt-2 text-xs text-slate-500 dark:text-navy-300 line-clamp-2 leading-relaxed">
                                                    {{ $tool->description }}
                                                </p>
                                            </div>

                                            {{-- Action Button --}}
                                            <div class="pt-6">
                                                @auth
                                                    @if($tool->is_maintenance)
                                                        <a href="{{ route('tool', $tool->slug) }}" class="btn h-9 w-full bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 text-xs rounded-lg">
                                                            Cek Status Maintenance
                                                        </a>
                                                    @elseif($tool->is_pro_only && !auth()->user()->isSubscribed() && !auth()->user()->is_admin)
                                                        <a href="{{ route('tool', $tool->slug) }}" class="btn h-9 w-full bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 text-xs rounded-lg flex items-center justify-center gap-1.5">
                                                            <x-lucide-lock class="size-3.5" />
                                                            <span>Buka Tool (PRO)</span>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('tool', $tool->slug) }}" class="btn h-9 w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90 text-xs rounded-lg flex items-center justify-center gap-1.5">
                                                            <span>Buka Tool</span>
                                                            <x-lucide-arrow-up-right class="size-3.5" />
                                                        </a>
                                                    @endif
                                                @else
                                                    {{-- Guest Trigger: Open Auth Gate Modal --}}
                                                    <button 
                                                        @click="openAuthGate('{{ addslashes($tool->name) }}', '{{ $tool->slug }}', '{{ $tool->category }}', {{ $tool->is_pro_only ? 'true' : 'false' }})"
                                                        class="btn h-9 w-full {{ $tool->is_pro_only ? 'bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450' : 'bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90' }} text-xs rounded-lg flex items-center justify-center gap-1.5">
                                                        @if($tool->is_pro_only)
                                                            <x-lucide-lock class="size-3.5" />
                                                            <span>Buka Tool (PRO)</span>
                                                        @else
                                                            <span>Buka Tool</span>
                                                            <x-lucide-arrow-up-right class="size-3.5" />
                                                        @endif
                                                    </button>
                                                @endauth
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </main>
    </div>

    {{-- Footer --}}
    <footer class="w-full bg-white dark:bg-navy-900 border-t border-slate-200 dark:border-navy-700 py-8 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500 dark:text-navy-300">
            <p>{{ $footerCopyright }}</p>
        </div>
    </footer>

    {{-- Auth Gate Modal (For Guest Users clicking 'Buka Tool') --}}
    <div 
        x-show="showAuthModal" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        {{-- Modal Content Card --}}
        <div 
            @click.outside="showAuthModal = false"
            x-show="showAuthModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="card w-full max-w-md p-6 sm:p-7 relative overflow-hidden bg-white dark:bg-navy-800 shadow-2xl border border-slate-200 dark:border-navy-700 text-center">

            {{-- Close Button --}}
            <button 
                @click="showAuthModal = false" 
                class="absolute right-4 top-4 btn size-8 rounded-full p-0 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:text-navy-300 dark:hover:bg-navy-700 dark:hover:text-navy-100">
                <x-lucide-x class="size-5" />
            </button>

            {{-- Icon Badge --}}
            <div class="mx-auto mb-4 mask is-squircle flex size-14 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                <template x-if="selectedToolIsPro">
                    <x-lucide-crown class="size-7 text-amber-500" />
                </template>
                <template x-if="!selectedToolIsPro">
                    <x-lucide-lock class="size-7" />
                </template>
            </div>

            {{-- Modal Title & Description --}}
            <h3 class="text-lg font-semibold text-slate-800 dark:text-navy-50">
                Akses <span class="text-primary dark:text-accent-light" x-text="selectedToolName"></span>
            </h3>
            <p class="mt-2 text-xs sm:text-sm text-slate-500 dark:text-navy-200 leading-relaxed">
                Silakan masuk ke akun Anda atau daftar gratis dalam hitungan detik untuk mulai menggunakan alat ini secara instan.
            </p>

            {{-- Features checkmarks --}}
            <div class="mt-5 rounded-lg bg-slate-50 p-3.5 dark:bg-navy-750 text-left text-xs space-y-2 border border-slate-150 dark:border-navy-600">
                <div class="flex items-center space-x-2 text-slate-700 dark:text-navy-100">
                    <x-lucide-check-circle-2 class="size-4 text-success shrink-0" />
                    <span>Akses cepat langsung di browser</span>
                </div>
                <div class="flex items-center space-x-2 text-slate-700 dark:text-navy-100">
                    <x-lucide-check-circle-2 class="size-4 text-success shrink-0" />
                    <span>Privasi terenkripsi & 100% aman</span>
                </div>
                <div class="flex items-center space-x-2 text-slate-700 dark:text-navy-100">
                    <x-lucide-check-circle-2 class="size-4 text-success shrink-0" />
                    <span>Gratis pendaftaran akun baru</span>
                </div>
            </div>

            {{-- Action Buttons (Login & Register) --}}
            <div class="mt-6 space-y-2.5">
                {{-- Option 1: Login --}}
                <a 
                    :href="'{{ route('login') }}?redirect=' + encodeURIComponent('/tool/' + selectedToolSlug)" 
                    class="btn w-full rounded-lg bg-primary font-medium text-white shadow-xs hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus h-10 text-xs sm:text-sm space-x-2 flex items-center justify-center">
                    <x-lucide-log-in class="size-4" />
                    <span>Sudah Punya Akun? Masuk</span>
                </a>

                {{-- Option 2: Register --}}
                <a 
                    :href="'{{ route('register') }}?redirect=' + encodeURIComponent('/tool/' + selectedToolSlug)" 
                    class="btn w-full rounded-lg border border-slate-300 bg-white font-medium text-slate-700 hover:bg-slate-50 focus:bg-slate-50 active:bg-slate-100 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100 dark:hover:bg-navy-600 h-10 text-xs sm:text-sm space-x-2 flex items-center justify-center">
                    <x-lucide-user-plus class="size-4 text-primary dark:text-accent-light" />
                    <span>Belum Punya Akun? Daftar Gratis</span>
                </a>
            </div>
        </div>
    </div>
</div>
