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

    {{-- Lineone Clean Dashboard Welcome Banner --}}
    <div class="card p-6 sm:p-8 lg:p-10 mb-8 border border-slate-150 dark:border-navy-600 bg-white dark:bg-navy-700">
        <div class="max-w-2xl mx-auto text-center">
            <h1 class="text-2xl font-semibold tracking-wide text-slate-800 dark:text-navy-50 sm:text-3xl">
                @if($isSuperAdmin)
                    Selamat Datang, <span class="text-primary dark:text-accent-light">{{ $user->name }}</span>
                @elseif($isProMax)
                    Studio Olah Dokumen <span class="text-primary dark:text-accent-light">Pro Max</span>
                @elseif($isPro)
                    Mau olah file apa hari ini, <span class="text-primary dark:text-accent-light">{{ $user->name }}</span>?
                @else
                    Mau olah file apa hari ini?
                @endif
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-slate-500 dark:text-navy-300 leading-relaxed">
                @if($isSuperAdmin)
                    Akses tak terbatas ke seluruh alat produktivitas, engine prioritas, dan konfigurasi sistem.
                @elseif($isProMax || $isPro)
                    Nikmati pemrosesan file berkapasitas besar tanpa batas kuota dengan prioritas server.
                @else
                    Pilih dan jalankan berbagai alat bantu kerja untuk kompresi, konversi dokumen, dan pengolahan gambar secara instan.
                @endif
            </p>

            {{-- Centered Hero Search Bar --}}
            <div class="relative mt-6 max-w-lg mx-auto">
                <input 
                    x-model="search"
                    type="text" 
                    placeholder="Cari alat produktivitas, format, atau fungsi file..." 
                    class="form-input h-11 w-full rounded-full border border-slate-300 bg-slate-50 px-4 pl-10 pr-10 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:text-navy-50 dark:placeholder:text-navy-400 dark:hover:border-navy-400 dark:focus:border-accent"
                />
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 flex items-center justify-center text-slate-400 dark:text-navy-300">
                    <x-lucide-search class="size-4" />
                </span>
                <button 
                    x-show="search.length > 0"
                    x-cloak
                    @click="search = ''" 
                    type="button" 
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-navy-100 transition-colors"
                >
                    <x-lucide-x class="size-4" />
                </button>
            </div>

            {{-- Category Filter Pills (Lineone Tab Button Style) --}}
            <div class="mt-6 flex flex-wrap gap-2 items-center justify-center">
                <button 
                    @click="activeCat = 'all'"
                    type="button"
                    :class="activeCat === 'all' ? 'bg-primary text-white dark:bg-accent' : 'bg-slate-150 text-slate-700 hover:bg-slate-200 dark:bg-navy-600 dark:text-navy-200 dark:hover:bg-navy-500'"
                    class="btn h-8 rounded-full px-3.5 text-xs font-medium transition-all"
                >
                    <span>Semua Tools</span>
                    <span class="ml-1 opacity-75">({{ $totalToolsCount }})</span>
                </button>

                @foreach($categories as $cat)
                    <button 
                        @click="activeCat = '{{ strtolower($cat['raw']) }}'"
                        type="button"
                        :class="activeCat === '{{ strtolower($cat['raw']) }}' ? 'bg-primary text-white dark:bg-accent' : 'bg-slate-150 text-slate-700 hover:bg-slate-200 dark:bg-navy-600 dark:text-navy-200 dark:hover:bg-navy-500'"
                        class="btn h-8 rounded-full px-3.5 text-xs font-medium transition-all"
                    >
                        <span>{{ $cat['name'] }}</span>
                        <span class="ml-1 opacity-75">({{ $cat['count'] }})</span>
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
                    <div class="mask is-squircle flex size-8 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-medium shrink-0">
                        @if(stripos($categoryName, 'image') !== false || stripos($categoryName, 'foto') !== false)
                            <x-lucide-image class="size-4.5" />
                        @elseif(stripos($categoryName, 'pdf') !== false || stripos($categoryName, 'doc') !== false)
                            <x-lucide-file-text class="size-4.5" />
                        @else
                            <x-lucide-boxes class="size-4.5" />
                        @endif
                    </div>
                    <div class="flex items-center gap-2.5 shrink-0">
                        <h2 class="text-base sm:text-lg font-medium tracking-wide text-slate-800 dark:text-navy-50">
                            {{ $catDisplayName }}
                        </h2>
                        <span class="badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-500 dark:text-navy-100 text-xs font-medium px-2.5 py-0.5">
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
                            class="tool-card card flex flex-col justify-between h-full group"
                        >
                            {{-- Top Image / Thumbnail --}}
                            <div class="flex h-44 items-center justify-center p-5 bg-slate-50 dark:bg-navy-800 rounded-t-lg relative overflow-hidden">
                                <img
                                    class="max-h-36 max-w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                    src="{{ $tool->image_url ?: asset('images/illustrations/creativedesign.svg') }}"
                                    alt="{{ $tool->name }}"
                                />

                                {{-- Badges Top Right --}}
                                <div class="absolute top-3 right-3 flex items-center gap-1">
                                    @if($tool->is_maintenance)
                                        <span class="badge rounded-full bg-warning/10 text-warning dark:bg-warning/15 text-xs font-medium px-2 py-0.5">
                                            Maintenance
                                        </span>
                                    @elseif($tool->is_pro_only)
                                        <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light text-xs font-medium px-2 py-0.5 uppercase">
                                            PRO
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
                            </div>

                            {{-- Card Body --}}
                            @php
                                $isProUser = $user && $user->isSubscribed();
                                $isAdmin = $user && $user->is_admin;
                                $isLockedForUser = $tool->is_pro_only && !$isProUser && !$isAdmin;
                            @endphp
                            <div class="flex flex-1 flex-col justify-between p-4 sm:p-5 text-center">
                                <div>
                                    <h3 class="text-base font-medium tracking-wide text-slate-700 dark:text-navy-100 line-clamp-1">
                                        {{ $tool->name }}
                                    </h3>
                                    <p class="pt-2 text-xs text-slate-500 dark:text-navy-300 line-clamp-2 leading-relaxed">
                                        {{ $tool->description }}
                                    </p>
                                </div>
                                <div class="pt-6">
                                    <a
                                        href="{{ route('tool', $tool->slug) }}" wire:navigate
                                        class="btn h-9 w-full {{ $isLockedForUser ? 'bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90' : 'bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90' }} rounded-lg text-xs flex items-center justify-center gap-1.5"
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

        {{-- Empty Search Result State --}}
        <div x-show="!hasAnyVisible()" x-cloak class="card p-10 text-center border border-slate-150 dark:border-navy-600 my-8">
            <div class="mask is-squircle size-14 bg-slate-100 text-slate-400 dark:bg-navy-600 dark:text-navy-300 flex items-center justify-center mx-auto mb-3">
                <x-lucide-search class="size-7" />
            </div>
            <h4 class="text-base font-medium tracking-wide text-slate-700 dark:text-navy-100">Tool Tidak Ditemukan</h4>
            <p class="text-slate-500 dark:text-navy-300 text-xs mt-1.5 max-w-sm mx-auto leading-relaxed">
                Tidak ada tool yang cocok dengan kriteria pencarian atau kategori yang dipilih.
            </p>
            <button 
                @click="search = ''; activeCat = 'all'" 
                type="button"
                class="btn h-8 rounded-full mt-4 bg-primary text-white dark:bg-accent px-4 text-xs font-medium mx-auto flex items-center gap-1.5"
            >
                <x-lucide-rotate-ccw class="size-3.5" />
                <span>Reset Filter & Pencarian</span>
            </button>
        </div>
    </div>
</div>
