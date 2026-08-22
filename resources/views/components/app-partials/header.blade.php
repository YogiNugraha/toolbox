@persist('header')
<nav class="header print:hidden">
    <!-- App Header  -->
    <div class="header-container relative flex w-full bg-white dark:bg-navy-750 print:hidden">
        <!-- Header Items -->
        <div class="flex w-full items-center justify-between">
            <!-- Left: Sidebar Toggle Button and Page Title -->
            <div class="flex items-center space-x-4">
                <div class="size-7">
                    <button
                        class="menu-toggle cursor-pointer ml-0.5 flex size-7 flex-col justify-center space-y-1.5 text-primary outline-hidden focus:outline-hidden dark:text-accent-light/80"
                        :class="$store.global.isSidebarExpanded && 'active'"
                        @click="$store.global.isSidebarExpanded = !$store.global.isSidebarExpanded">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50"
                    x-data
                    x-on:livewire:navigated.window="$nextTick(() => { $el.innerText = document.getElementById('page-title-source')?.innerText || '' })">
                    @yield('page_title', request()->is('admin*') ? 'Admin Panel' : 'Dashboard')
                </h2>
            </div>

            <!-- Right: Header buttons -->
            <div class="-mr-1.5 flex items-center space-x-2">
                <!-- Mobile Search Toggle -->
                <button @click="$store.global.isSearchbarActive = !$store.global.isSearchbarActive"
                    class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25 sm:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5.5 text-slate-500 dark:text-navy-100"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>

                <!-- Main Searchbar -->
                @php
                    $headerTools = \App\Models\Tool::getActiveTools()->map(function($t) {
                        return [
                            'id' => $t->id,
                            'name' => $t->name,
                            'slug' => $t->slug,
                            'description' => $t->description,
                            'category' => $t->category,
                            'badge' => $t->badge,
                            'is_highlighted' => (bool) $t->is_highlighted,
                            'is_maintenance' => (bool) $t->is_maintenance,
                            'image_url' => $t->image_url,
                        ];
                    });
                    $headerCategories = $headerTools->pluck('category')->unique()->values();
                @endphp
                <template x-if="$store.breakpoints.smAndUp">
                    <div class="flex" 
                         x-data="{
                             ...usePopper({ placement: 'bottom-end', offset: 12 }),
                             searchQuery: '',
                             selectedCat: 'all',
                             tools: {{ json_encode($headerTools) }},
                             categories: {{ json_encode($headerCategories) }},
                             get filteredTools() {
                                 const q = this.searchQuery.trim().toLowerCase();
                                 return this.tools.filter(t => {
                                     const matchCat = this.selectedCat === 'all' || t.category === this.selectedCat;
                                     const matchQuery = !q || 
                                         t.name.toLowerCase().includes(q) || 
                                         t.slug.toLowerCase().includes(q) || 
                                         (t.description && t.description.toLowerCase().includes(q)) ||
                                         (t.category && t.category.toLowerCase().includes(q));
                                     return matchCat && matchQuery;
                                 });
                             }
                         }" 
                         @click.outside="if(isShowPopper) isShowPopper = false"
                         @keydown.escape.window="isShowPopper = false">
                        
                        <div class="relative mr-4 flex h-8">
                            <input 
                                x-model="searchQuery"
                                placeholder="Cari tool di sini..."
                                class="form-input peer h-full rounded-full bg-slate-150 px-4 pl-9 pr-7 text-xs-plus text-slate-800 ring-primary/50 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:text-navy-100 dark:placeholder-navy-300 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 transition-all duration-200"
                                :class="isShowPopper ? 'w-80' : 'w-60'" 
                                @focus="isShowPopper = true" 
                                type="text"
                                x-ref="popperRef" 
                            />
                            <div class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                <x-lucide-search class="size-4 transition-colors duration-200" />
                            </div>
                            <template x-if="searchQuery">
                                <button @click="searchQuery = ''" type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-navy-100">
                                    <x-lucide-x class="size-3.5" />
                                </button>
                            </template>
                        </div>

                        <div :class="isShowPopper && 'show'" class="popper-root" x-ref="popperRoot">
                            <div class="popper-box flex max-h-[calc(100vh-6rem)] w-96 flex-col rounded-xl border border-slate-150 bg-white shadow-soft dark:border-navy-600 dark:bg-navy-700 dark:shadow-soft-dark overflow-hidden">
                                {{-- Category Filter Tabs --}}
                                <div class="is-scrollbar-hidden flex shrink-0 overflow-x-auto border-b border-slate-150 bg-slate-50 px-2 py-1.5 dark:border-navy-600 dark:bg-navy-800 text-xs gap-1">
                                    <button 
                                        @click="selectedCat = 'all'"
                                        :class="selectedCat === 'all' ? 'bg-primary text-white dark:bg-accent font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-200/70 dark:text-navy-200 dark:hover:bg-navy-700 font-medium'"
                                        class="btn h-7 rounded-lg px-2.5 text-xs transition-all shrink-0">
                                        Semua (<span x-text="tools.length"></span>)
                                    </button>
                                    <template x-for="cat in categories" :key="cat">
                                        <button 
                                            @click="selectedCat = cat"
                                            :class="selectedCat === cat ? 'bg-primary text-white dark:bg-accent font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-200/70 dark:text-navy-200 dark:hover:bg-navy-700 font-medium'"
                                            class="btn h-7 rounded-lg px-2.5 text-xs transition-all shrink-0"
                                            x-text="cat">
                                        </button>
                                    </template>
                                </div>

                                {{-- Result List --}}
                                <div class="is-scrollbar-hidden overflow-y-auto overscroll-contain p-2 max-h-80 space-y-1">
                                    <template x-for="tool in filteredTools" :key="tool.id">
                                        <a :href="'/tool/' + tool.slug" 
                                           class="group flex items-center justify-between p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-navy-600/80 transition-colors">
                                            <div class="flex items-center space-x-2.5 min-w-0">
                                                {{-- Tool Thumbnail / Icon --}}
                                                <div class="size-8 rounded-lg overflow-hidden bg-slate-100 dark:bg-navy-800 p-0.5 border border-slate-200 dark:border-navy-600 shrink-0 flex items-center justify-center">
                                                    <template x-if="tool.image_url">
                                                        <img :src="tool.image_url" :alt="tool.name" class="size-full object-contain" />
                                                    </template>
                                                    <template x-if="!tool.image_url">
                                                        <x-lucide-wrench class="size-4 text-primary dark:text-accent-light" />
                                                    </template>
                                                </div>

                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="font-bold text-slate-800 dark:text-navy-100 text-xs truncate group-hover:text-primary dark:group-hover:text-accent-light transition-colors" x-text="tool.name"></span>
                                                        <span class="badge rounded-full bg-slate-150 text-slate-600 dark:bg-navy-800 dark:text-navy-300 text-[9px] font-semibold px-1.5 py-0.2" x-text="tool.category"></span>
                                                        <template x-if="tool.badge">
                                                            <span class="badge rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 text-[9px] font-black px-1.5 py-0.2" x-text="tool.badge"></span>
                                                        </template>
                                                        <template x-if="tool.is_maintenance">
                                                            <span class="badge rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 text-[9px] font-bold px-1.5 py-0.2">Maintenance</span>
                                                        </template>
                                                    </div>
                                                    <p class="text-[11px] text-slate-400 dark:text-navy-300 truncate mt-0.5" x-text="tool.description || ('Buka alat ' + tool.name)"></p>
                                                </div>
                                            </div>

                                            <div class="shrink-0 pl-2 text-slate-300 group-hover:text-primary dark:text-navy-400 dark:group-hover:text-accent-light transition-colors">
                                                <x-lucide-chevron-right class="size-4" />
                                            </div>
                                        </a>
                                    </template>

                                    {{-- Empty Search Result --}}
                                    <div x-show="filteredTools.length === 0" class="py-8 text-center text-xs text-slate-400 dark:text-navy-300">
                                        <x-lucide-search class="size-8 mx-auto text-slate-300 dark:text-navy-400 mb-2 opacity-60" />
                                        <p class="font-semibold text-slate-700 dark:text-navy-100">Tool Tidak Ditemukan</p>
                                        <p class="text-[11px] mt-0.5">Tidak ada tool yang cocok dengan "<span class="font-bold text-slate-600 dark:text-navy-200" x-text="searchQuery"></span>".</p>
                                    </div>
                                </div>

                                {{-- Popper Footer --}}
                                <div class="flex items-center justify-between border-t border-slate-150 bg-slate-50 px-3 py-2 text-[10px] text-slate-400 dark:border-navy-600 dark:bg-navy-800 dark:text-navy-300">
                                    <span>Ditemukan <strong class="text-slate-700 dark:text-navy-100" x-text="filteredTools.length"></strong> tools</span>
                                    <span class="text-slate-400">Tekan <kbd class="rounded bg-slate-200 dark:bg-navy-600 px-1 py-0.5 font-mono text-[9px]">ESC</kbd> untuk menutup</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Dark Mode Toggle -->
                <button @click="$store.global.isDarkModeEnabled = !$store.global.isDarkModeEnabled"
                    class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                    <svg x-show="$store.global.isDarkModeEnabled"
                        x-transition:enter="transition-transform duration-200 ease-out absolute origin-top"
                        x-transition:enter-start="scale-75" x-transition:enter-end="scale-100 static"
                        class="size-6 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M11.75 3.412a.818.818 0 01-.07.917 6.332 6.332 0 00-1.4 3.971c0 3.564 2.98 6.494 6.706 6.494a6.86 6.86 0 002.856-.617.818.818 0 011.1 1.047C19.593 18.614 16.218 21 12.283 21 7.18 21 3 16.973 3 11.956c0-4.563 3.46-8.31 7.925-8.948a.818.818 0 01.826.404z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" x-show="!$store.global.isDarkModeEnabled"
                        x-transition:enter="transition-transform duration-200 ease-out absolute origin-top"
                        x-transition:enter-start="scale-75" x-transition:enter-end="scale-100 static"
                        class="size-6 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                <!-- Profile -->
                @php
                    $isProUser = auth()->check() && auth()->user()->isSubscribed();
                    $activePlanName = auth()->check() ? auth()->user()->activePlanName() : null;
                    $activePlanSlug = auth()->check() ? auth()->user()->activePlanSlug() : null;
                    $isProMax = $activePlanSlug === 'pro-max' || strtolower($activePlanName) === 'pro max';
                    $subExpiry = auth()->check() && auth()->user()->activeSubscription() ? auth()->user()->activeSubscription()->expires_at : null;
                @endphp
                <div x-data="usePopper({ placement: 'bottom-end', offset: 12 })" @click.outside="if(isShowPopper) isShowPopper = false" class="flex">
                    <button @click="isShowPopper = !isShowPopper" x-ref="popperRef" class="avatar size-8 cursor-pointer relative rounded-full {{ $isProUser ? ($isProMax ? 'ring-2 ring-purple-500 ring-offset-2 ring-offset-white dark:ring-offset-navy-750' : 'ring-2 ring-amber-400 ring-offset-2 ring-offset-white dark:ring-offset-navy-750') : '' }}">
                        @if(auth()->check() && auth()->user()->profile_photo_path)
                            <img class="rounded-full object-cover" src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="avatar" />
                        @else
                            <div class="rounded-full {{ $isProUser ? ($isProMax ? 'bg-gradient-to-tr from-purple-600 to-indigo-500 text-white' : 'bg-gradient-to-tr from-amber-400 to-orange-500 text-white') : 'bg-slate-200 text-slate-500 dark:bg-navy-500 dark:text-navy-100' }} w-full h-full flex items-center justify-center font-bold text-xs">
                                {{ auth()->check() ? substr(auth()->user()->name, 0, 1) : 'U' }}
                            </div>
                        @endif

                        @if($isProUser)
                            <span class="absolute -top-1 -right-1 flex size-3.5 items-center justify-center rounded-full {{ $isProMax ? 'bg-linear-to-r from-amber-400 to-yellow-300 text-slate-900' : 'bg-amber-400 text-slate-900' }} shadow-xs ring-1 ring-white dark:ring-navy-700">
                                @if($isProMax)
                                    <x-lucide-crown class="size-2.5 stroke-[2.5]" />
                                @else
                                    <x-lucide-star class="size-2.5 stroke-[2.5] fill-current" />
                                @endif
                            </span>
                        @else
                            <span class="absolute right-0 size-2.5 rounded-full border-2 border-white bg-success dark:border-navy-700"></span>
                        @endif
                    </button>
                    <div :class="isShowPopper && 'show'" class="popper-root fixed" x-ref="popperRoot">
                        <div class="popper-box w-72 rounded-xl border border-slate-150 bg-white shadow-soft dark:border-navy-600 dark:bg-navy-700 overflow-hidden">
                            <div class="flex items-center space-x-3.5 rounded-t-xl {{ $isProUser ? ($isProMax ? 'bg-gradient-to-r from-purple-50 via-indigo-50 to-amber-50/50 dark:from-purple-950/40 dark:via-navy-800 dark:to-navy-800' : 'bg-gradient-to-r from-amber-50/80 to-orange-50/40 dark:from-amber-950/30 dark:to-navy-800') : 'bg-slate-100 dark:bg-navy-800' }} py-4.5 px-4 border-b border-slate-150 dark:border-navy-600">
                                <div class="avatar size-12 shrink-0 relative rounded-full {{ $isProUser ? ($isProMax ? 'ring-2 ring-purple-500 ring-offset-2 ring-offset-white dark:ring-offset-navy-800' : 'ring-2 ring-amber-400 ring-offset-2 ring-offset-white dark:ring-offset-navy-800') : '' }}">
                                    @if(auth()->check() && auth()->user()->profile_photo_path)
                                        <img class="rounded-full object-cover" src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="avatar" />
                                    @else
                                        <div class="rounded-full {{ $isProUser ? ($isProMax ? 'bg-gradient-to-tr from-purple-600 to-indigo-500 text-white' : 'bg-gradient-to-tr from-amber-400 to-orange-500 text-white') : 'bg-slate-200 text-slate-500 dark:bg-navy-500 dark:text-navy-100' }} w-full h-full flex items-center justify-center font-bold text-base">
                                            {{ auth()->check() ? substr(auth()->user()->name, 0, 1) : 'U' }}
                                        </div>
                                    @endif
                                </div>
                                <div class="overflow-hidden flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('profile') }}" class="text-sm font-bold text-slate-700 hover:text-primary focus:text-primary dark:text-navy-100 dark:hover:text-accent-light dark:focus:text-accent-light truncate" wire:navigate>
                                            {{ auth()->check() ? auth()->user()->name : 'User' }}
                                        </a>
                                    </div>
                                    <p class="text-xs text-slate-400 dark:text-navy-300 truncate">
                                        {{ auth()->check() ? auth()->user()->email : '' }}
                                    </p>
                                    <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                        @if($isProUser)
                                            @if($isProMax)
                                                <span class="badge rounded-full bg-linear-to-r from-amber-500 via-purple-600 to-indigo-600 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-white shadow-xs inline-flex items-center gap-1">
                                                    <x-lucide-crown class="size-3 stroke-[2.5]" />
                                                    <span>{{ $activePlanName }}</span>
                                                </span>
                                            @else
                                                <span class="badge rounded-full bg-linear-to-r from-amber-500 to-orange-500 px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-white shadow-xs inline-flex items-center gap-1">
                                                    <x-lucide-star class="size-3 stroke-[2.5] fill-current" />
                                                    <span>{{ $activePlanName }}</span>
                                                </span>
                                            @endif
                                            @if($subExpiry)
                                                <span class="text-[10px] text-slate-400 dark:text-navy-300 font-medium">
                                                    s/d {{ $subExpiry->translatedFormat('d M Y') }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge rounded-full bg-slate-200 dark:bg-navy-600 px-2 py-0.5 text-[9px] font-bold text-slate-600 dark:text-navy-200">
                                                FREE PLAN
                                            </span>
                                            <a href="{{ route('pricing') }}" wire:navigate class="text-[10px] text-primary hover:underline dark:text-accent-light font-bold">
                                                Upgrade &rarr;
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col pt-2 pb-5"
                                 x-data="{ isAdminPath: window.location.pathname.startsWith('/admin') }"
                                 x-init="isAdminPath = window.location.pathname.startsWith('/admin')"
                                 x-on:livewire:navigated.window="isAdminPath = window.location.pathname.startsWith('/admin')">
                                @if(auth()->check() && auth()->user()->is_admin)
                                <a :href="isAdminPath ? '{{ route('dashboard') }}' : '{{ route('admin.overview') }}'" class="group flex items-center space-x-3 py-2 px-4 tracking-wide outline-hidden transition-all hover:bg-slate-100 focus:bg-slate-100 dark:hover:bg-navy-600 dark:focus:bg-navy-600" wire:navigate>
                                    <div class="flex size-8 items-center justify-center rounded-lg bg-info text-white">
                                        <template x-if="isAdminPath">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                            </svg>
                                        </template>
                                        <template x-if="!isAdminPath">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </template>
                                    </div>
                                    <div>
                                        <h2 class="font-medium text-slate-700 transition-colors group-hover:text-primary group-focus:text-primary dark:text-navy-100 dark:group-hover:text-accent-light dark:group-focus:text-accent-light" x-text="isAdminPath ? 'Kembali ke Dashboard' : 'Admin Panel'"></h2>
                                        <div class="text-xs text-slate-400 line-clamp-1 dark:text-navy-300" x-text="isAdminPath ? 'Halaman utama user' : 'Manajemen sistem'"></div>
                                    </div>
                                </a>
                                @endif
                                <a href="{{ route('profile') }}" class="group flex items-center space-x-3 py-2 px-4 tracking-wide outline-hidden transition-all hover:bg-slate-100 focus:bg-slate-100 dark:hover:bg-navy-600 dark:focus:bg-navy-600" wire:navigate>
                                    <div class="flex size-8 items-center justify-center rounded-lg bg-warning text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="font-medium text-slate-700 transition-colors group-hover:text-primary group-focus:text-primary dark:text-navy-100 dark:group-hover:text-accent-light dark:group-focus:text-accent-light">Edit Profil</h2>
                                        <div class="text-xs text-slate-400 line-clamp-1 dark:text-navy-300">Pengaturan profil Anda</div>
                                    </div>
                                </a>
                                <div class="mt-3 px-4">
                                    <form id="logout-form" method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="button" @click="$store.confirmModal.open('Yakin mau keluar?', 'Sesi anda akan diakhiri.', 'submit-form', { formId: 'logout-form' })" class="btn h-9 w-full space-x-2 bg-primary text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            <span>Logout</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
@endpersist
