<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard - ' . config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|inter:400,500,600|jetbrains-mono:400,500"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-paper text-ink font-sans antialiased">
    <div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden print:h-auto print:overflow-visible print:block">
        
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-[84px]'" class="print:hidden bg-ink text-slate-300 flex flex-col h-full shrink-0 transition-all duration-300 overflow-hidden relative">
            <div class="px-6 py-5 border-b border-white/10 shrink-0 flex items-center h-[73px]">
                <a href="{{ route('dashboard') }}" wire:navigate class="font-display font-bold text-white text-lg tracking-tight flex items-center gap-3 overflow-hidden whitespace-nowrap">
                    <x-lucide-package class="w-7 h-7 text-white/70 shrink-0 -ml-0.5" />
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms>{{ config('app.name') }}</span>
                </a>
            </div>

            <nav class="flex-1 py-4 space-y-1 overflow-y-auto overflow-x-hidden scrollbar-hide">
                <div class="px-6 pb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider" x-show="sidebarOpen">
                    Admin Panel
                </div>
                
                <a href="{{ route('admin.overview') }}" wire:navigate title="Overview"
                    class="flex items-center gap-4 px-6 py-3 text-sm transition-colors border-l-2 {{ request()->routeIs('admin.overview') ? 'text-white font-medium bg-white/5 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent' }}">
                    <x-lucide-layout-dashboard class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Overview</span>
                </a>
                
                <a href="{{ route('admin.users') }}" wire:navigate title="Pengguna"
                    class="flex items-center gap-4 px-6 py-3 text-sm transition-colors border-l-2 {{ request()->routeIs('admin.users') ? 'text-white font-medium bg-white/5 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent' }}">
                    <x-lucide-users class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Pengguna</span>
                </a>
                
                <a href="{{ route('admin.transactions') }}" wire:navigate title="Transaksi"
                    class="flex items-center gap-4 px-6 py-3 text-sm transition-colors border-l-2 {{ request()->routeIs('admin.transactions') ? 'text-white font-medium bg-white/5 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent' }}">
                    <x-lucide-receipt class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap flex-1">Transaksi</span>
                </a>
            </nav>

            <div class="py-4 border-t border-white/10 shrink-0 overflow-hidden">
                <a href="{{ route('profile') }}" wire:navigate title="Profil Saya"
                    class="flex items-center gap-4 px-6 py-3 text-sm transition-colors border-l-2 {{ request()->routeIs('profile') ? 'text-white font-medium bg-white/5 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent' }}">
                    <x-lucide-user class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Profil Saya</span>
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout"
                        class="w-full text-left flex items-center gap-4 px-6 py-3 text-sm text-slate-400 hover:text-white hover:bg-white/5 transition-colors border-l-2 border-transparent">
                        <x-lucide-log-out class="w-5 h-5 shrink-0" />
                        <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="flex-1 flex flex-col h-full overflow-hidden print:h-auto print:overflow-visible print:block">
            <!-- Topbar -->
            <header class="print:hidden bg-paper border-b border-hairline px-8 py-4 flex justify-between items-center shrink-0 h-[73px]">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="cursor-pointer text-ink-muted hover:text-ink transition-colors p-1 -ml-2 rounded-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                        <x-lucide-menu class="w-6 h-6" />
                    </button>
                    <h1 class="font-display font-bold text-xl text-ink">@yield('page_title', 'Dashboard')</h1>
                </div>

                <div x-data="{ 
                        name: '{{ addslashes(auth()->user()->name) }}',
                        photoUrl: '{{ auth()->user()->profile_photo_path ? Storage::url(auth()->user()->profile_photo_path) : '' }}',
                        initials() { return this.name ? this.name.substring(0, 1) : ''; },
                        dropdownOpen: false
                    }" 
                    @profile-updated.window="name = $event.detail.name || $event.detail[0]?.name || name"
                    @profile-photo-updated.window="photoUrl = $event.detail.path || $event.detail[0]?.path || photoUrl"
                    class="relative flex items-center gap-4">
                    
                    <button @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false" class="flex items-center gap-3 focus:outline-none transition-opacity hover:opacity-80">
                        <span class="text-sm font-medium text-ink hidden sm:block" x-text="name"></span>
                        <div class="w-8 h-8 rounded-full bg-amber/15 border border-amber/40 text-amber font-mono text-sm flex items-center justify-center overflow-hidden">
                            <img x-show="photoUrl" :src="photoUrl" :alt="name" class="w-full h-full object-cover" style="display: none;" x-cloak>
                            <span x-show="!photoUrl" x-text="initials()"></span>
                        </div>
                        <x-lucide-chevron-down class="w-4 h-4 text-ink-muted transition-transform duration-200 hidden sm:block" x-bind:class="dropdownOpen ? 'rotate-180' : ''" />
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="dropdownOpen"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 top-full mt-2 w-48 bg-white rounded-sm border border-hairline shadow-lg py-1 z-50"
                         style="display: none;" x-cloak>
                        
                        <div class="px-4 py-3 border-b border-hairline bg-paper/50">
                            <p class="text-xs text-ink-muted mb-1">Paket Saat Ini</p>
                            @if(auth()->user()->activeSubscription())
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-ink">Pro</span>
                                    <span class="text-[10px] bg-amber/20 text-amber px-1.5 py-0.5 rounded-sm border border-amber/30">AKTIF</span>
                                </div>
                            @else
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-ink">Free</span>
                                    <a href="{{ route('pricing') }}" class="text-[10px] bg-amber text-ink px-2 py-0.5 rounded-sm hover:bg-amber/90">Upgrade</a>
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ route('dashboard.billing') }}" wire:navigate class="flex items-center gap-3 px-4 py-2 text-sm text-ink hover:bg-paper transition-colors mt-1">
                            <x-lucide-credit-card class="w-4 h-4 text-ink-muted" />
                            Billing & Pro
                        </a>

                        <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-3 px-4 py-2 text-sm text-ink hover:bg-paper transition-colors">
                            <x-lucide-user class="w-4 h-4 text-ink-muted" />
                            Profil Saya
                        </a>
                        
                        <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 px-4 py-2 text-sm text-ink hover:bg-paper transition-colors border-t border-hairline border-b">
                            <x-lucide-arrow-left class="w-4 h-4 text-ink-muted" />
                            Kembali ke User Dashboard
                        </a>
                        
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors text-left">
                                <x-lucide-log-out class="w-4 h-4" />
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Main content -->
            <main class="flex-1 overflow-y-auto bg-paper flex flex-col print:overflow-visible print:bg-white print:block">
                <div class="w-full px-8 py-8 print:p-0">
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
