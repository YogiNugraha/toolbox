<div class="sidebar-panel">
    <div class="flex h-full grow flex-col bg-white pl-[var(--main-sidebar-width)] dark:bg-navy-750">
        <!-- Sidebar Panel Header -->
        <div class="flex h-18 w-full items-center justify-between pl-4 pr-1">
            <p class="text-base tracking-wider text-slate-800 dark:text-navy-100">
                Menu Utama
            </p>
            <button @click="$store.global.isSidebarExpanded = false"
                class="btn size-7 rounded-full p-0 text-primary hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:text-accent-light/80 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25 xl:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Panel Body -->
        <div class="h-[calc(100%-4.5rem)] overflow-x-hidden pb-6" x-data="{ expandedItem: null }" x-init="$el._x_simplebar = new SimpleBar($el);">
            <ul class="flex flex-1 flex-col px-4 font-inter">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="flex text-xs-plus py-2 tracking-wide outline-hidden transition-colors duration-300 ease-in-out {{ request()->routeIs('dashboard') ? 'text-primary dark:text-accent-light font-medium' : 'text-slate-600 hover:text-slate-800 dark:text-navy-200 dark:hover:text-navy-50' }}">
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('history') }}"
                        class="flex text-xs-plus py-2 tracking-wide outline-hidden transition-colors duration-300 ease-in-out {{ request()->routeIs('history') ? 'text-primary dark:text-accent-light font-medium' : 'text-slate-600 hover:text-slate-800 dark:text-navy-200 dark:hover:text-navy-50' }}">
                        Riwayat
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.billing') }}"
                        class="flex text-xs-plus py-2 tracking-wide outline-hidden transition-colors duration-300 ease-in-out {{ request()->routeIs('dashboard.billing') || request()->routeIs('dashboard.invoice') ? 'text-primary dark:text-accent-light font-medium' : 'text-slate-600 hover:text-slate-800 dark:text-navy-200 dark:hover:text-navy-50' }}">
                        Billing & Paket
                    </a>
                </li>

                <!-- Divider Kumpulan Tools -->
                <li class="pt-4 pb-1">
                    <div class="flex items-center space-x-2">
                        <div class="h-px flex-1 bg-slate-200 dark:bg-navy-600"></div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">Kumpulan Tools</span>
                        <div class="h-px flex-1 bg-slate-200 dark:bg-navy-600"></div>
                    </div>
                </li>

                @php
                    $activeTools = \App\Models\Tool::getActiveTools();
                    $groupedCategories = $activeTools->groupBy('category');
                @endphp

                @foreach($groupedCategories as $categoryName => $catTools)
                    @php
                        $categorySlug = \Illuminate\Support\Str::slug($categoryName);
                        $isCatActive = request()->is('category/' . $categorySlug) || request()->is('category/' . strtolower($categoryName));
                        $displayName = $categoryName;
                        if (strtolower($categoryName) === 'image') $displayName = 'Gambar & Foto';
                        if (strtolower($categoryName) === 'document') $displayName = 'Dokumen & PDF';
                    @endphp
                    <li>
                        <a href="{{ route('dashboard.category', $categorySlug) }}"
                            class="flex items-center justify-between text-xs-plus py-2 tracking-wide outline-hidden transition-colors duration-300 ease-in-out {{ $isCatActive ? 'text-primary dark:text-accent-light font-medium' : 'text-slate-600 hover:text-slate-800 dark:text-navy-200 dark:hover:text-navy-50' }}">
                            <div class="flex items-center space-x-2">
                                <div class="size-1.5 rounded-full border border-current opacity-40"></div>
                                <span>{{ $displayName }}</span>
                            </div>
                            <span class="badge rounded-full {{ $isCatActive ? 'bg-primary/10 text-primary dark:bg-accent/20 dark:text-accent-light' : 'bg-slate-150 text-slate-600 dark:bg-navy-600 dark:text-navy-200' }} text-[10px] font-bold px-1.5 py-0.5">
                                {{ $catTools->count() }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if(auth()->check() && auth()->user()->is_admin)
            <div class="my-3 mx-4 h-px bg-slate-200 dark:bg-navy-500"></div>
            <ul class="flex flex-1 flex-col px-4 font-inter">
                <li>
                    <a href="{{ route('admin.overview') }}"
                        class="flex text-xs-plus py-2 tracking-wide outline-hidden transition-colors duration-300 ease-in-out {{ request()->routeIs('admin.overview') ? 'text-primary dark:text-accent-light font-medium' : 'text-slate-600 hover:text-slate-800 dark:text-navy-200 dark:hover:text-navy-50' }}">
                        Statistik
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.tools') }}"
                        class="flex text-xs-plus py-2 tracking-wide outline-hidden transition-colors duration-300 ease-in-out {{ request()->routeIs('admin.tools') ? 'text-primary dark:text-accent-light font-medium' : 'text-slate-600 hover:text-slate-800 dark:text-navy-200 dark:hover:text-navy-50' }}">
                        Kelola Tools
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.plans') }}"
                        class="flex text-xs-plus py-2 tracking-wide outline-hidden transition-colors duration-300 ease-in-out {{ request()->routeIs('admin.plans') ? 'text-primary dark:text-accent-light font-medium' : 'text-slate-600 hover:text-slate-800 dark:text-navy-200 dark:hover:text-navy-50' }}">
                        Paket & Harga
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users') }}"
                        class="flex text-xs-plus py-2 tracking-wide outline-hidden transition-colors duration-300 ease-in-out {{ request()->routeIs('admin.users') ? 'text-primary dark:text-accent-light font-medium' : 'text-slate-600 hover:text-slate-800 dark:text-navy-200 dark:hover:text-navy-50' }}">
                        Pengguna
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.transactions') }}"
                        class="flex text-xs-plus py-2 tracking-wide outline-hidden transition-colors duration-300 ease-in-out {{ request()->routeIs('admin.transactions') ? 'text-primary dark:text-accent-light font-medium' : 'text-slate-600 hover:text-slate-800 dark:text-navy-200 dark:hover:text-navy-50' }}">
                        Transaksi
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.settings') }}"
                        class="flex text-xs-plus py-2 tracking-wide outline-hidden transition-colors duration-300 ease-in-out {{ request()->routeIs('admin.settings') ? 'text-primary dark:text-accent-light font-medium' : 'text-slate-600 hover:text-slate-800 dark:text-navy-200 dark:hover:text-navy-50' }}">
                        Pengaturan Global
                    </a>
                </li>
            </ul>
            @endif
        </div>
    </div>
</div>
