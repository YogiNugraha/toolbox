<div>
    @section('title', 'Dashboard - ' . config('app.name'))
    @section('page_title', 'Dashboard')

    {{-- Canva-Inspired Hero Banner Section --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 bg-gradient-to-br from-cyan-50/60 via-indigo-50/70 to-purple-50/70 p-6 text-center shadow-xs dark:border-navy-600/70 dark:bg-gradient-to-br dark:from-navy-800 dark:via-navy-750 dark:to-navy-800 sm:p-10 lg:p-12 mb-8">
        {{-- Subtle decorative ambient background blurs --}}
        <div class="pointer-events-none absolute -top-12 -left-12 size-48 rounded-full bg-primary/10 blur-3xl dark:bg-accent/10"></div>
        <div class="pointer-events-none absolute -bottom-12 -right-12 size-48 rounded-full bg-secondary/10 blur-3xl dark:bg-secondary/10"></div>

        <div class="relative z-1 max-w-3xl mx-auto">
            {{-- Main Canva-style Greeting Title --}}
            <h1 class="text-2xl font-black tracking-tight text-slate-800 dark:text-navy-50 sm:text-3xl lg:text-4xl">
                Mau olah file apa hari ini?
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-slate-500 dark:text-navy-300 max-w-md mx-auto leading-relaxed">
                Pilih dan jalankan berbagai alat bantu kerja untuk kompresi gambar, konversi dokumen & PDF secara instan.
            </p>

            {{-- Centered Hero Search Bar --}}
            <div class="relative mt-6 max-w-2xl mx-auto">
                <input 
                    wire:model.live.debounce.250ms="search" 
                    type="text" 
                    placeholder="Cari alat produktivitas, format, atau fungsi file..." 
                    class="form-input h-12 sm:h-13 w-full rounded-full border border-slate-200/90 bg-white px-5 pl-12 pr-11 text-xs-plus sm:text-sm text-slate-800 shadow-sm placeholder:text-slate-400 hover:border-slate-300 focus:border-primary focus:ring-4 focus:ring-primary/15 dark:border-navy-500 dark:bg-navy-900 dark:text-navy-50 dark:placeholder:text-navy-400 dark:hover:border-navy-400 dark:focus:border-accent dark:focus:ring-accent/15 transition-all duration-200"
                />
                <span class="pointer-events-none absolute left-4.5 top-1/2 -translate-y-1/2 flex items-center justify-center text-slate-400 dark:text-navy-300">
                    <x-lucide-search class="size-5" />
                </span>
                @if($search)
                    <button 
                        wire:click="$set('search', '')" 
                        type="button" 
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-navy-100 transition-colors p-1"
                    >
                        <x-lucide-x class="size-4" />
                    </button>
                @endif
            </div>

            {{-- Category Filter Navigation (Canva-style Icon Chips) --}}
            <div class="is-scrollbar-hidden mt-7 flex items-center justify-center gap-3 overflow-x-auto pb-1 sm:gap-4">
                {{-- Chip: Semua --}}
                <button 
                    wire:click="selectCategory('all')"
                    type="button"
                    class="group flex flex-col items-center gap-1.5 shrink-0 px-2 py-1 transition-transform active:scale-95"
                >
                    <div class="mask is-squircle flex size-12 items-center justify-center transition-all duration-200 {{ $selectedCategory === 'all' ? 'bg-primary text-white shadow-md shadow-primary/40 ring-2 ring-primary ring-offset-2 ring-offset-white dark:bg-accent dark:ring-accent dark:shadow-accent/40 dark:ring-offset-navy-750' : 'bg-white text-slate-600 shadow-xs border border-slate-200/80 hover:bg-slate-50 dark:bg-navy-700 dark:text-navy-200 dark:border-navy-600 dark:hover:bg-navy-650' }}">
                        <x-lucide-layout-grid class="size-5.5" />
                    </div>
                    <span class="text-xs font-semibold {{ $selectedCategory === 'all' ? 'text-primary dark:text-accent font-bold' : 'text-slate-600 dark:text-navy-300 group-hover:text-slate-800' }}">
                        Semua
                    </span>
                    <span class="badge rounded-full px-1.5 py-0.2 text-[9px] font-bold {{ $selectedCategory === 'all' ? 'bg-primary/10 text-primary dark:bg-accent/15 dark:text-accent' : 'bg-slate-150 text-slate-500 dark:bg-navy-700 dark:text-navy-300' }}">
                        {{ $totalToolsCount }}
                    </span>
                </button>

                {{-- Dynamic Category Chips --}}
                @foreach($categories as $cat)
                    @php
                        $isSelected = strtolower($selectedCategory) === strtolower($cat['raw']) || strtolower($selectedCategory) === strtolower($cat['slug']);
                        $isImage = stripos($cat['raw'], 'image') !== false || stripos($cat['raw'], 'foto') !== false;
                        $isDoc = stripos($cat['raw'], 'pdf') !== false || stripos($cat['raw'], 'doc') !== false;
                    @endphp
                    <button 
                        wire:click="selectCategory('{{ $cat['raw'] }}')"
                        type="button"
                        class="group flex flex-col items-center gap-1.5 shrink-0 px-2 py-1 transition-transform active:scale-95"
                    >
                        <div class="mask is-squircle flex size-12 items-center justify-center transition-all duration-200 {{ $isSelected ? 'bg-primary text-white shadow-md shadow-primary/40 ring-2 ring-primary ring-offset-2 ring-offset-white dark:bg-accent dark:ring-accent dark:shadow-accent/40 dark:ring-offset-navy-750' : 'bg-white text-slate-600 shadow-xs border border-slate-200/80 hover:bg-slate-50 dark:bg-navy-700 dark:text-navy-200 dark:border-navy-600 dark:hover:bg-navy-650' }}">
                            @if($isImage)
                                <x-lucide-image class="size-5.5 {{ $isSelected ? 'text-white' : 'text-blue-500 dark:text-blue-400' }}" />
                            @elseif($isDoc)
                                <x-lucide-file-text class="size-5.5 {{ $isSelected ? 'text-white' : 'text-purple-500 dark:text-purple-400' }}" />
                            @else
                                <x-lucide-boxes class="size-5.5 {{ $isSelected ? 'text-white' : 'text-amber-500 dark:text-amber-400' }}" />
                            @endif
                        </div>
                        <span class="text-xs font-semibold {{ $isSelected ? 'text-primary dark:text-accent font-bold' : 'text-slate-600 dark:text-navy-300 group-hover:text-slate-800' }}">
                            {{ $cat['name'] }}
                        </span>
                        <span class="badge rounded-full px-1.5 py-0.2 text-[9px] font-bold {{ $isSelected ? 'bg-primary/10 text-primary dark:bg-accent/15 dark:text-accent' : 'bg-slate-150 text-slate-500 dark:bg-navy-700 dark:text-navy-300' }}">
                            {{ $cat['count'] }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Tools Collection Grouped Section by Section with Dividers --}}
    <div class="space-y-12">
        @forelse($groupedTools as $categoryName => $toolsInCategory)
            @php
                $catDisplayName = $categoryName;
                if (strtolower($categoryName) === 'image') $catDisplayName = 'Gambar & Foto';
                if (strtolower($categoryName) === 'document') $catDisplayName = 'Dokumen & PDF';
            @endphp

            <div class="space-y-5">
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

                {{-- Cards Grid (Lineone Onboarding-1 Standard) --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 sm:gap-5 lg:gap-6">
                    @foreach($toolsInCategory as $tool)
                        @php
                            $defaultIllustration = match($tool->slug) {
                                'compress-image' => asset('images/illustrations/upload-cloud.svg'),
                                'convert-image' => asset('images/illustrations/responsive.svg'),
                                'pdf-to-word' => asset('images/illustrations/writer.svg'),
                                default => asset('images/illustrations/creativedesign.svg'),
                            };

                            $imageSrc = $tool->image_url ?: $defaultIllustration;
                        @endphp

                        <div class="card flex flex-col justify-between h-full hover:shadow-lg transition-all duration-200 border border-slate-150/70 dark:border-navy-600/70 group">
                            {{-- Top Image / Thumbnail --}}
                            <div class="flex h-44 items-center justify-center p-5 bg-slate-50/60 dark:bg-navy-800/40 rounded-t-lg relative overflow-hidden">
                                <img
                                    class="max-h-36 max-w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                    src="{{ $imageSrc }}"
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
                                $user = auth()->user();
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
        @empty
            {{-- Empty Search Result State --}}
            <div class="card p-10 text-center border border-slate-200 dark:border-navy-600 my-8">
                <div class="mask is-squircle size-16 bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300 flex items-center justify-center mx-auto mb-3">
                    <x-lucide-search class="size-8" />
                </div>
                <h4 class="text-base font-bold text-slate-700 dark:text-navy-100">Tool Tidak Ditemukan</h4>
                <p class="text-slate-400 dark:text-navy-300 text-xs mt-1.5 max-w-sm mx-auto leading-relaxed">
                    @if($search)
                        Tidak ada tool yang cocok dengan kata kunci "<span class="font-semibold text-slate-600 dark:text-navy-200">{{ $search }}</span>".
                    @else
                        Belum ada tool yang tersedia di kategori ini.
                    @endif
                </p>
                <button 
                    wire:click="resetFilters" 
                    type="button"
                    class="btn rounded-full mt-4 bg-primary text-white dark:bg-accent h-8.5 px-5 text-xs font-semibold mx-auto shadow-md shadow-primary/30 dark:shadow-accent/30 flex items-center gap-1.5"
                >
                    <x-lucide-rotate-ccw class="size-3.5" />
                    <span>Reset Filter & Pencarian</span>
                </button>
            </div>
        @endforelse
    </div>
</div>
