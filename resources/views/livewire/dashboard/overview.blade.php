<div x-data="{ 
    activeCat: 'all', 
    search: '',
    matches(cat, title, desc, slug) {
        let catMatch = (this.activeCat === 'all' || this.activeCat.toLowerCase() === cat.toLowerCase());
        if (!catMatch) return false;
        if (!this.search || this.search.trim() === '') return true;
        let q = this.search.toLowerCase().trim();
        return title.toLowerCase().includes(q) || desc.toLowerCase().includes(q) || slug.toLowerCase().includes(q) || cat.toLowerCase().includes(q);
    },
    hasVisibleInCat(cat) {
        let cards = Array.from($el.querySelectorAll('.tool-card[data-cat=\'' + cat.toLowerCase() + '\']'));
        if (this.activeCat !== 'all' && this.activeCat.toLowerCase() !== cat.toLowerCase()) return false;
        if (!this.search || this.search.trim() === '') return cards.length > 0;
        let q = this.search.toLowerCase().trim();
        return cards.some(c => {
            let t = (c.getAttribute('data-title') || '').toLowerCase();
            let d = (c.getAttribute('data-desc') || '').toLowerCase();
            let s = (c.getAttribute('data-slug') || '').toLowerCase();
            return t.includes(q) || d.includes(q) || s.includes(q);
        });
    },
    hasAnyVisible() {
        let cards = Array.from($el.querySelectorAll('.tool-card'));
        if (cards.length === 0) return true; // during initial render
        return cards.some(c => {
            let cat = (c.getAttribute('data-cat') || '').toLowerCase();
            let t = (c.getAttribute('data-title') || '').toLowerCase();
            let d = (c.getAttribute('data-desc') || '').toLowerCase();
            let s = (c.getAttribute('data-slug') || '').toLowerCase();
            let catMatch = (this.activeCat === 'all' || this.activeCat.toLowerCase() === cat);
            if (!catMatch) return false;
            if (!this.search || this.search.trim() === '') return true;
            let q = this.search.toLowerCase().trim();
            return t.includes(q) || d.includes(q) || s.includes(q);
        });
    }
}">
    @section('title', 'Dashboard - ' . config('app.name'))
    @section('page_title', 'Dashboard')

    @php
        $user = auth()->user();
        $isSuperAdmin = $user && $user->is_admin;
        $isSubscribed = $user && $user->isSubscribed();
        $activePlanSlug = $user ? $user->activePlanSlug() : 'free';
        $activePlanName = $user ? $user->activePlanName() : 'Free';
        $isProMax = $activePlanSlug === 'pro-max' || strtolower((string)$activePlanName) === 'pro max';
        $isPro = ($isSubscribed && !$isSuperAdmin && !$isProMax) || $activePlanSlug === 'pro';
    @endphp

    {{-- Canva-Inspired Hero Banner Section (Customized by Plan / Admin Tier) --}}
    <div class="relative overflow-hidden rounded-3xl p-6 text-center sm:p-10 lg:p-12 mb-8 transition-all duration-300
        @if($isSuperAdmin)
            border border-rose-500/30 bg-gradient-to-br from-rose-950/20 via-purple-950/15 to-slate-900/30 dark:from-rose-950/50 dark:via-purple-950/40 dark:to-navy-900 shadow-xl shadow-rose-500/10 ring-1 ring-rose-500/20
        @elseif($isProMax)
            border border-purple-500/30 bg-gradient-to-br from-purple-900/20 via-indigo-950/20 to-amber-500/10 dark:from-purple-950/50 dark:via-indigo-950/40 dark:to-navy-900 shadow-xl shadow-purple-500/10 ring-1 ring-purple-500/20
        @elseif($isPro)
            border border-amber-400/30 bg-gradient-to-br from-amber-500/15 via-orange-500/10 to-indigo-900/15 dark:from-amber-950/40 dark:via-orange-950/30 dark:to-navy-900 shadow-xl shadow-amber-500/10 ring-1 ring-amber-400/20
        @else
            border border-slate-200/70 bg-gradient-to-br from-cyan-50/60 via-indigo-50/70 to-purple-50/70 shadow-xs dark:border-navy-600/70 dark:bg-gradient-to-br dark:from-navy-800 dark:via-navy-750 dark:to-navy-800
        @endif
    ">
        {{-- High-tech Cyber / Ambient Glow for Admin & Pro Max & Pro --}}
        @if($isSuperAdmin)
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(#f43f5e_1px,transparent_1px)] [background-size:24px_24px] opacity-20 dark:opacity-30"></div>
            <div class="pointer-events-none absolute -top-24 -left-24 size-72 rounded-full bg-rose-500/25 blur-3xl animate-pulse"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-24 size-72 rounded-full bg-purple-600/25 blur-3xl"></div>
            <div class="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 size-96 rounded-full bg-indigo-500/10 blur-3xl"></div>
        @elseif($isProMax)
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(#a855f7_1px,transparent_1px)] [background-size:24px_24px] opacity-20 dark:opacity-30"></div>
            <div class="pointer-events-none absolute -top-24 -left-24 size-72 rounded-full bg-purple-600/25 blur-3xl animate-pulse"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-24 size-72 rounded-full bg-amber-400/20 blur-3xl"></div>
        @elseif($isPro)
            <div class="pointer-events-none absolute -top-20 -left-20 size-64 rounded-full bg-amber-400/25 blur-3xl animate-pulse"></div>
            <div class="pointer-events-none absolute -bottom-20 -right-20 size-64 rounded-full bg-orange-500/20 blur-3xl"></div>
        @else
            <div class="pointer-events-none absolute -top-12 -left-12 size-48 rounded-full bg-primary/10 blur-3xl dark:bg-accent/10"></div>
            <div class="pointer-events-none absolute -bottom-12 -right-12 size-48 rounded-full bg-secondary/10 blur-3xl dark:bg-secondary/10"></div>
        @endif

        <div class="relative z-1 max-w-3xl mx-auto">
            {{-- Main Greeting Title --}}
            <h1 class="text-2xl font-black tracking-tight text-slate-800 dark:text-navy-50 sm:text-3xl lg:text-4xl">
                @if($isSuperAdmin)
                    Selamat Datang, <span class="bg-gradient-to-r from-rose-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent">{{ $user->name }}</span>!
                @elseif($isProMax)
                    Studio Olah Dokumen <span class="bg-gradient-to-r from-purple-600 via-indigo-500 to-amber-500 bg-clip-text text-transparent">Pro Max</span>
                @elseif($isPro)
                    Mau olah file apa hari ini, <span class="bg-gradient-to-r from-amber-500 to-orange-500 bg-clip-text text-transparent">{{ $user->name }}</span>?
                @else
                    Mau olah file apa hari ini?
                @endif
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-slate-500 dark:text-navy-300 max-w-md mx-auto leading-relaxed">
                @if($isSuperAdmin)
                    Anda memiliki akses tak terbatas ke seluruh alat produktivitas, engine konversi kecepatan tinggi, dan konfigurasi sistem.
                @elseif($isProMax || $isPro)
                    Nikmati pemrosesan file tanpa batas kuota dengan kualitas output terbaik dan prioritas server.
                @else
                    Pilih dan jalankan berbagai alat bantu kerja untuk kompresi gambar, konversi dokumen & PDF secara instan.
                @endif
            </p>

            {{-- Centered Hero Search Bar --}}
            <div class="relative mt-6 max-w-2xl mx-auto">
                <input 
                    x-model="search"
                    type="text" 
                    placeholder="Cari alat produktivitas, format, atau fungsi file..." 
                    class="form-input h-12 sm:h-13 w-full rounded-full border px-5 pl-12 pr-11 text-xs-plus sm:text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:ring-4 transition-all duration-200
                        {{ $isSuperAdmin ? 'border-rose-500/30 bg-white/90 focus:border-rose-500 focus:ring-rose-500/15 dark:border-rose-500/40 dark:bg-navy-900/90 dark:text-white backdrop-blur-md' : ($isProMax ? 'border-purple-500/30 bg-white/90 focus:border-purple-500 focus:ring-purple-500/15 dark:border-purple-500/40 dark:bg-navy-900/90 dark:text-white backdrop-blur-md' : ($isPro ? 'border-amber-400/30 bg-white/90 focus:border-amber-500 focus:ring-amber-500/15 dark:border-amber-400/40 dark:bg-navy-900/90 dark:text-white backdrop-blur-md' : 'border-slate-200/90 bg-white hover:border-slate-300 focus:border-primary focus:ring-primary/15 dark:border-navy-500 dark:bg-navy-900 dark:text-navy-50 dark:placeholder:text-navy-400 dark:hover:border-navy-400 dark:focus:border-accent dark:focus:ring-accent/15')) }}
                    "
                />
                <span class="pointer-events-none absolute left-4.5 top-1/2 -translate-y-1/2 flex items-center justify-center text-slate-400 dark:text-navy-300">
                    <x-lucide-search class="size-5 {{ $isSuperAdmin ? 'text-rose-500' : ($isProMax ? 'text-purple-500' : ($isPro ? 'text-amber-500' : '')) }}" />
                </span>
                <button 
                    x-show="search.length > 0"
                    x-cloak
                    @click="search = ''" 
                    type="button" 
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-navy-100 transition-colors p-1"
                >
                    <x-lucide-x class="size-4" />
                </button>
            </div>

            {{-- Category Filter Navigation (Canva-style Icon Chips - Instant 0ms Filtering) --}}
            <div class="is-scrollbar-hidden mt-7 flex items-center justify-center gap-3 overflow-x-auto pb-1 sm:gap-4">
                {{-- Chip: Semua --}}
                <button 
                    @click="activeCat = 'all'"
                    type="button"
                    class="group flex flex-col items-center gap-1.5 shrink-0 px-2 py-1 transition-transform active:scale-95 cursor-pointer"
                >
                    <div 
                        class="mask is-squircle flex size-12 items-center justify-center transition-all duration-200"
                        :class="activeCat === 'all' ? '{{ $isSuperAdmin ? "bg-gradient-to-r from-rose-600 to-indigo-600 text-white shadow-md shadow-rose-500/40 ring-2 ring-rose-500 ring-offset-2 ring-offset-white dark:ring-offset-navy-750" : ($isProMax ? "bg-gradient-to-r from-purple-600 to-amber-500 text-white shadow-md shadow-purple-500/40 ring-2 ring-purple-500 ring-offset-2 ring-offset-white dark:ring-offset-navy-750" : ($isPro ? "bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-md shadow-amber-500/40 ring-2 ring-amber-400 ring-offset-2 ring-offset-white dark:ring-offset-navy-750" : "bg-primary text-white shadow-md shadow-primary/40 ring-2 ring-primary ring-offset-2 ring-offset-white dark:bg-accent dark:ring-accent dark:shadow-accent/40 dark:ring-offset-navy-750")) }}' : 'bg-white/90 text-slate-600 shadow-xs border border-slate-200/80 hover:bg-slate-50 dark:bg-navy-700/90 dark:text-navy-200 dark:border-navy-600 dark:hover:bg-navy-650 backdrop-blur-sm'"
                    >
                        <x-lucide-layout-grid class="size-5.5" />
                    </div>
                    <span 
                        class="text-xs font-semibold"
                        :class="activeCat === 'all' ? '{{ $isSuperAdmin ? "text-rose-600 dark:text-rose-400 font-bold" : ($isProMax ? "text-purple-600 dark:text-purple-400 font-bold" : ($isPro ? "text-amber-600 dark:text-amber-400 font-bold" : "text-primary dark:text-accent font-bold")) }}' : 'text-slate-600 dark:text-navy-300 group-hover:text-slate-800'"
                    >
                        Semua
                    </span>
                    <span 
                        class="badge rounded-full px-1.5 py-0.2 text-[9px] font-bold"
                        :class="activeCat === 'all' ? '{{ $isSuperAdmin ? "bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-300" : ($isProMax ? "bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-300" : ($isPro ? "bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300" : "bg-primary/10 text-primary dark:bg-accent/15 dark:text-accent")) }}' : 'bg-slate-150 text-slate-500 dark:bg-navy-700 dark:text-navy-300'"
                    >
                        {{ $totalToolsCount }}
                    </span>
                </button>

                {{-- Dynamic Category Chips --}}
                @foreach($categories as $cat)
                    @php
                        $isImage = stripos($cat['raw'], 'image') !== false || stripos($cat['raw'], 'foto') !== false;
                        $isDoc = stripos($cat['raw'], 'pdf') !== false || stripos($cat['raw'], 'doc') !== false;
                    @endphp
                    <button 
                        @click="activeCat = '{{ strtolower($cat['raw']) }}'"
                        type="button"
                        class="group flex flex-col items-center gap-1.5 shrink-0 px-2 py-1 transition-transform active:scale-95 cursor-pointer"
                    >
                        <div 
                            class="mask is-squircle flex size-12 items-center justify-center transition-all duration-200"
                            :class="activeCat === '{{ strtolower($cat['raw']) }}' ? '{{ $isSuperAdmin ? "bg-gradient-to-r from-rose-600 to-indigo-600 text-white shadow-md shadow-rose-500/40 ring-2 ring-rose-500 ring-offset-2 ring-offset-white dark:ring-offset-navy-750" : ($isProMax ? "bg-gradient-to-r from-purple-600 to-amber-500 text-white shadow-md shadow-purple-500/40 ring-2 ring-purple-500 ring-offset-2 ring-offset-white dark:ring-offset-navy-750" : ($isPro ? "bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-md shadow-amber-500/40 ring-2 ring-amber-400 ring-offset-2 ring-offset-white dark:ring-offset-navy-750" : "bg-primary text-white shadow-md shadow-primary/40 ring-2 ring-primary ring-offset-2 ring-offset-white dark:bg-accent dark:ring-accent dark:shadow-accent/40 dark:ring-offset-navy-750")) }}' : 'bg-white/90 text-slate-600 shadow-xs border border-slate-200/80 hover:bg-slate-50 dark:bg-navy-700/90 dark:text-navy-200 dark:border-navy-600 dark:hover:bg-navy-650 backdrop-blur-sm'"
                        >
                            @if($isImage)
                                <span class="flex items-center justify-center size-5.5" :class="activeCat === '{{ strtolower($cat['raw']) }}' ? 'text-white' : 'text-blue-500 dark:text-blue-400'">
                                    <x-lucide-image class="size-5.5" />
                                </span>
                            @elseif($isDoc)
                                <span class="flex items-center justify-center size-5.5" :class="activeCat === '{{ strtolower($cat['raw']) }}' ? 'text-white' : 'text-purple-500 dark:text-purple-400'">
                                    <x-lucide-file-text class="size-5.5" />
                                </span>
                            @else
                                <span class="flex items-center justify-center size-5.5" :class="activeCat === '{{ strtolower($cat['raw']) }}' ? 'text-white' : 'text-amber-500 dark:text-amber-400'">
                                    <x-lucide-boxes class="size-5.5" />
                                </span>
                            @endif
                        </div>
                        <span 
                            class="text-xs font-semibold"
                            :class="activeCat === '{{ strtolower($cat['raw']) }}' ? '{{ $isSuperAdmin ? "text-rose-600 dark:text-rose-400 font-bold" : ($isProMax ? "text-purple-600 dark:text-purple-400 font-bold" : ($isPro ? "text-amber-600 dark:text-amber-400 font-bold" : "text-primary dark:text-accent font-bold")) }}' : 'text-slate-600 dark:text-navy-300 group-hover:text-slate-800'"
                        >
                            {{ $cat['name'] }}
                        </span>
                        <span 
                            class="badge rounded-full px-1.5 py-0.2 text-[9px] font-bold"
                            :class="activeCat === '{{ strtolower($cat['raw']) }}' ? '{{ $isSuperAdmin ? "bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-300" : ($isProMax ? "bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-300" : ($isPro ? "bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300" : "bg-primary/10 text-primary dark:bg-accent/15 dark:text-accent")) }}' : 'bg-slate-150 text-slate-500 dark:bg-navy-700 dark:text-navy-300'"
                        >
                            {{ $cat['count'] }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Tools Collection Grouped Section by Section with Dividers --}}
    <div class="space-y-12">
        @foreach($groupedTools as $categoryName => $toolsInCategory)
            @php
                $catDisplayName = $categoryName;
                if (strtolower($categoryName) === 'image') $catDisplayName = 'Gambar & Foto';
                if (strtolower($categoryName) === 'document') $catDisplayName = 'Dokumen & PDF';
            @endphp

            <div 
                x-show="hasVisibleInCat('{{ strtolower($categoryName) }}')"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-5"
            >
                {{-- Category Header & Divider --}}
                <div class="flex items-center space-x-3 pt-2">
                    <div class="mask is-squircle flex size-9 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-bold shrink-0">
                        @if(stripos($categoryName, 'image') !== false || stripos($categoryName, 'foto') !== false)
                            <x-lucide-image class="size-5" />
                        @elseif(stripos($categoryName, 'pdf') !== false || stripos($categoryName, 'doc') !== false)
                            <x-lucide-file-text class="size-5" />
                        @else
                            <x-lucide-boxes class="size-5" />
                        @endif
                    </div>
                    <div class="flex items-center gap-2.5 shrink-0">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-navy-100 tracking-tight">
                            {{ $catDisplayName }}
                        </h2>
                        <span class="badge rounded-full bg-slate-150 text-slate-600 dark:bg-navy-600 dark:text-navy-200 text-[10px] font-bold px-2 py-0.5">
                            {{ $toolsInCategory->count() }} Tools
                        </span>
                    </div>
                    <div class="h-px flex-1 bg-slate-200 dark:bg-navy-600"></div>
                </div>

                {{-- Cards Grid --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 sm:gap-5 lg:gap-6">
                    @foreach($toolsInCategory as $tool)
                        <div 
                            data-cat="{{ strtolower($tool->category) }}"
                            data-title="{{ $tool->name }}"
                            data-desc="{{ $tool->description }}"
                            data-slug="{{ $tool->slug }}"
                            x-show="matches('{{ strtolower($tool->category) }}', '{{ addslashes($tool->name) }}', '{{ addslashes($tool->description ?? '') }}', '{{ addslashes($tool->slug) }}')"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            class="tool-card card flex flex-col justify-between h-full hover:shadow-lg transition-all duration-200 border border-slate-150/70 dark:border-navy-600/70 group"
                        >
                            {{-- Top Image / Thumbnail --}}
                            <div class="flex h-44 items-center justify-center p-5 bg-slate-50/60 dark:bg-navy-800/40 rounded-t-lg relative overflow-hidden">
                                <img
                                    class="max-h-36 max-w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                    src="{{ $tool->image_url ?: asset('images/illustrations/creativedesign.svg') }}"
                                    alt="{{ $tool->name }}"
                                />

                                {{-- Badges Top Right --}}
                                <div class="absolute top-3 right-3 flex items-center gap-1">
                                    @if($tool->is_maintenance)
                                        <span class="badge rounded-full bg-warning/15 text-warning text-[10px] font-bold px-2 py-0.5 shadow-xs">
                                            Maintenance
                                        </span>
                                    @elseif($tool->is_pro_only)
                                        <span class="badge rounded-full bg-linear-to-r from-amber-500 via-purple-600 to-indigo-600 text-white text-[10px] font-black px-2.5 py-0.5 shadow-xs">
                                            👑 PRO
                                        </span>
                                    @elseif($tool->badge === 'HOT')
                                        <span class="badge rounded-full bg-linear-to-r from-red-500 to-orange-500 text-white text-[10px] font-black px-2 py-0.5 shadow-xs">
                                            🔥 HOT
                                        </span>
                                    @elseif($tool->badge === 'NEW')
                                        <span class="badge rounded-full bg-linear-to-r from-emerald-500 to-teal-500 text-white text-[10px] font-black px-2 py-0.5 shadow-xs">
                                            ✨ NEW
                                        </span>
                                    @elseif($tool->badge === 'PRO')
                                        <span class="badge rounded-full bg-linear-to-r from-amber-500 to-purple-600 text-white text-[10px] font-black px-2 py-0.5 shadow-xs">
                                            👑 PRO
                                        </span>
                                    @elseif($tool->badge)
                                        <span class="badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-500 dark:text-navy-200 text-[10px] font-bold px-2 py-0.5">
                                            {{ $tool->badge }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Card Body --}}
                            @php
                                $isProUser = $user && $user->isSubscribed();
                                $isAdmin = $user && $user->is_admin;
                                $isLockedForUser = $tool->is_pro_only && !$isProUser && !$isAdmin;
                            @endphp
                            <div class="flex flex-1 flex-col justify-between px-4.5 pb-6 pt-4 text-center sm:px-5">
                                <div>
                                    <h3 class="text-base font-bold text-slate-700 dark:text-navy-100 line-clamp-1">
                                        {{ $tool->name }}
                                    </h3>
                                    <p class="pt-2 text-xs text-slate-500 dark:text-navy-300 line-clamp-2 leading-relaxed">
                                        {{ $tool->description }}
                                    </p>
                                </div>
                                <div class="pt-6">
                                    <a
                                        href="{{ route('tool', $tool->slug) }}" wire:navigate
                                        class="btn w-full {{ $isLockedForUser ? 'bg-linear-to-r from-purple-600 via-indigo-600 to-amber-500 text-white shadow-lg shadow-purple-500/25 hover:opacity-95' : 'bg-primary font-medium text-white shadow-lg shadow-primary/30 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/30 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90' }} rounded-lg text-xs py-2.5 flex items-center justify-center gap-1.5 transition-all"
                                    >
                                        @if($isLockedForUser)
                                            <x-lucide-lock class="size-3.5" />
                                            <span>Buka Tool (PRO)</span>
                                        @else
                                            <span>Buka Tool</span>
                                            <x-lucide-arrow-up-right class="size-3.5" />
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Empty Search Result State (Client-side instant fallback) --}}
        <div x-show="!hasAnyVisible()" x-cloak class="card p-10 text-center border border-slate-200 dark:border-navy-600 my-8">
            <div class="mask is-squircle size-16 bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300 flex items-center justify-center mx-auto mb-3">
                <x-lucide-search class="size-8" />
            </div>
            <h4 class="text-base font-bold text-slate-700 dark:text-navy-100">Tool Tidak Ditemukan</h4>
            <p class="text-slate-400 dark:text-navy-300 text-xs mt-1.5 max-w-sm mx-auto leading-relaxed">
                Tidak ada tool yang cocok dengan kriteria pencarian atau kategori yang dipilih.
            </p>
            <button 
                @click="search = ''; activeCat = 'all'" 
                type="button"
                class="btn rounded-full mt-4 bg-primary text-white dark:bg-accent h-8.5 px-5 text-xs font-semibold mx-auto shadow-md shadow-primary/30 dark:shadow-accent/30 flex items-center gap-1.5 cursor-pointer"
            >
                <x-lucide-rotate-ccw class="size-3.5" />
                <span>Reset Filter & Pencarian</span>
            </button>
        </div>
    </div>
</div>
