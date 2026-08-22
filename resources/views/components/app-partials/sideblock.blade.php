<div class="sidebar sidebar-panel print:hidden">
    <div class="flex h-full grow flex-col border-r border-slate-150 bg-white dark:border-navy-700 dark:bg-navy-750">
        <div class="flex items-center justify-between px-3 pt-4">
            <!-- Application Logo -->
            @php
                $siteName = \App\Models\Setting::get('site_name', \App\Models\Setting::get('brand_name', config('app.name')));
                $siteLogo = \App\Models\Setting::get('site_logo');
            @endphp
            <div class="flex">
                <a href="/" wire:navigate class="flex items-center space-x-3">
                    @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                        <div class="flex size-10 shrink-0 items-center justify-center">
                            <img class="size-full object-contain" src="{{ \Illuminate\Support\Facades\Storage::url($siteLogo) }}" alt="{{ $siteName }}" />
                        </div>
                    @else
                        <img class="size-10 transition-transform duration-500 ease-in-out hover:rotate-[360deg]"
                            src="{{ asset('images/app-logo.svg') }}" alt="logo" />
                    @endif
                    <span class="text-lg font-bold uppercase tracking-wider text-slate-700 dark:text-navy-100">
                        {{ $siteName }}
                    </span>
                </a>
            </div>
            <button @click="$store.global.isSidebarExpanded = false"
                class="btn size-7 rounded-full p-0 text-primary hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:text-accent-light/80 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25 xl:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </div>
        <div class="h-[calc(100%-4.5rem)] overflow-x-hidden pb-6 mt-5" x-data="{ expandedItem: null }" x-init="$el._x_simplebar = new SimpleBar($el);">
            @if(request()->is('admin*'))
            <ul class="flex flex-1 flex-col px-4 space-y-1.5 font-inter font-medium">
                <li>
                    <a href="{{ route('admin.overview') }}" wire:navigate
                        class="flex items-center space-x-2 rounded-lg px-4 py-2.5 tracking-wide outline-hidden transition-all {{ request()->routeIs('admin.overview') ? 'bg-primary text-white dark:bg-accent' : 'group text-slate-600 hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-600 dark:hover:text-navy-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 {{ request()->routeIs('admin.overview') ? '' : 'text-slate-400 transition-colors group-hover:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Statistik</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.tools') }}" wire:navigate
                        class="flex items-center space-x-2 rounded-lg px-4 py-2.5 tracking-wide outline-hidden transition-all {{ request()->routeIs('admin.tools') ? 'bg-primary text-white dark:bg-accent' : 'group text-slate-600 hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-600 dark:hover:text-navy-100' }}">
                        <x-lucide-wrench class="size-5 {{ request()->routeIs('admin.tools') ? '' : 'text-slate-400 transition-colors group-hover:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200' }}" />
                        <span>Kelola Tools</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.plans') }}" wire:navigate
                        class="flex items-center space-x-2 rounded-lg px-4 py-2.5 tracking-wide outline-hidden transition-all {{ request()->routeIs('admin.plans') ? 'bg-primary text-white dark:bg-accent' : 'group text-slate-600 hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-600 dark:hover:text-navy-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 {{ request()->routeIs('admin.plans') ? '' : 'text-slate-400 transition-colors group-hover:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <span>Paket & Harga</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users') }}" wire:navigate
                        class="flex items-center space-x-2 rounded-lg px-4 py-2.5 tracking-wide outline-hidden transition-all {{ request()->routeIs('admin.users') ? 'bg-primary text-white dark:bg-accent' : 'group text-slate-600 hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-600 dark:hover:text-navy-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 {{ request()->routeIs('admin.users') ? '' : 'text-slate-400 transition-colors group-hover:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Pengguna</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.transactions') }}" wire:navigate
                        class="flex items-center space-x-2 rounded-lg px-4 py-2.5 tracking-wide outline-hidden transition-all {{ request()->routeIs('admin.transactions') ? 'bg-primary text-white dark:bg-accent' : 'group text-slate-600 hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-600 dark:hover:text-navy-100' }}">
                        <x-lucide-receipt class="size-5 {{ request()->routeIs('admin.transactions') ? '' : 'text-slate-400 transition-colors group-hover:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200' }}" />
                        <span>Transaksi</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.settings') }}" wire:navigate
                        class="flex items-center space-x-2 rounded-lg px-4 py-2.5 tracking-wide outline-hidden transition-all {{ request()->routeIs('admin.settings') ? 'bg-primary text-white dark:bg-accent' : 'group text-slate-600 hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-600 dark:hover:text-navy-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 {{ request()->routeIs('admin.settings') ? '' : 'text-slate-400 transition-colors group-hover:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Pengaturan Global</span>
                    </a>
                </li>
            </ul>
            @else
            <ul class="flex flex-1 flex-col px-4 space-y-1.5 font-inter font-medium">
                <li>
                    <a href="{{ route('dashboard') }}" wire:navigate
                        class="flex items-center space-x-2 rounded-lg px-4 py-2.5 tracking-wide outline-hidden transition-all {{ request()->routeIs('dashboard') || request()->is('tool/*') || request()->is('category/*') ? 'bg-primary text-white dark:bg-accent' : 'group text-slate-600 hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-600 dark:hover:text-navy-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 {{ request()->routeIs('dashboard') || request()->is('tool/*') || request()->is('category/*') ? '' : 'text-slate-400 transition-colors group-hover:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2zM14 13a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1h-4a1 1 0 01-1-1v-5z" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('history') }}" wire:navigate
                        class="flex items-center space-x-2 rounded-lg px-4 py-2.5 tracking-wide outline-hidden transition-all {{ request()->routeIs('history') ? 'bg-primary text-white dark:bg-accent' : 'group text-slate-600 hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-600 dark:hover:text-navy-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 {{ request()->routeIs('history') ? '' : 'text-slate-400 transition-colors group-hover:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Riwayat</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.billing') }}" wire:navigate
                        class="flex items-center space-x-2 rounded-lg px-4 py-2.5 tracking-wide outline-hidden transition-all {{ request()->routeIs('dashboard.billing') || request()->routeIs('dashboard.invoice') ? 'bg-primary text-white dark:bg-accent' : 'group text-slate-600 hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-600 dark:hover:text-navy-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 {{ request()->routeIs('dashboard.billing') || request()->routeIs('dashboard.invoice') ? '' : 'text-slate-400 transition-colors group-hover:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span>Billing & Paket</span>
                    </a>
                </li>
            </ul>
            @endif
        </div>
    </div>
</div>
