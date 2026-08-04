<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard - ToolBox')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|inter:400,500,600|jetbrains-mono:400,500"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-paper text-ink font-sans antialiased">
    <div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-[84px]'" class="bg-ink text-slate-300 flex flex-col h-full shrink-0 transition-all duration-300 overflow-hidden relative">
            <div class="px-6 py-5 border-b border-white/10 shrink-0 flex items-center h-[73px]">
                <a href="{{ route('dashboard') }}" wire:navigate class="font-display font-bold text-white text-lg tracking-tight flex items-center gap-3 overflow-hidden whitespace-nowrap">
                    <x-lucide-package class="w-7 h-7 text-white/70 shrink-0 -ml-0.5" />
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms>ToolBox</span>
                </a>
            </div>

            <nav class="flex-1 py-4 space-y-1 overflow-y-auto overflow-x-hidden scrollbar-hide">
                <a href="{{ route('dashboard') }}" wire:navigate title="Dashboard"
                    class="flex items-center gap-4 px-6 py-3 text-sm transition-colors border-l-2 {{ request()->routeIs('dashboard') ? 'text-white font-medium bg-white/5 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent' }}">
                    <x-lucide-layout-dashboard class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Dashboard</span>
                </a>
                
                <a href="{{ route('history') }}" wire:navigate title="Riwayat"
                    class="flex items-center gap-4 px-6 py-3 text-sm transition-colors border-l-2 {{ request()->routeIs('history') ? 'text-white font-medium bg-white/5 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent' }}">
                    <x-lucide-history class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Riwayat</span>
                </a>
                
                <a href="{{ route('dashboard.billing') }}" wire:navigate title="Billing & Pro"
                    class="flex items-center gap-4 px-6 py-3 text-sm transition-colors border-l-2 {{ request()->routeIs('dashboard.billing') ? 'text-white font-medium bg-white/5 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent' }}">
                    <x-lucide-credit-card class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap flex-1">Billing</span>
                    @if (auth()->user()->activeSubscription())
                        <span x-show="sidebarOpen" class="px-1.5 py-0.5 text-[10px] bg-amber/20 text-amber border border-amber/30 rounded-sm font-bold uppercase tracking-wider">PRO</span>
                    @endif
                </a>

                <div x-data="{ toolsOpen: {{ request()->is('tool/*') ? 'true' : 'false' }} }" class="mt-2 border-t border-white/10 pt-4">
                    <button @click="sidebarOpen ? toolsOpen = !toolsOpen : (sidebarOpen = true, toolsOpen = true)" 
                            class="w-full flex items-center justify-between gap-4 px-6 py-3 text-sm transition-colors border-l-2 {{ request()->is('tool/*') ? 'text-white bg-white/5 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent' }}">
                        <div class="flex items-center gap-4">
                            <x-lucide-layers class="w-5 h-5 shrink-0" />
                            <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap font-medium">Kumpulan Tools</span>
                        </div>
                        <x-lucide-chevron-down x-show="sidebarOpen" class="w-4 h-4 shrink-0 transition-transform duration-200" x-bind:class="toolsOpen ? 'rotate-180' : ''" />
                    </button>

                    <div x-show="toolsOpen && sidebarOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="bg-black/20 py-2 mt-1 space-y-1" x-cloak>
                        
                        @foreach (config('tools') as $tool)
                            <a href="{{ route('tool', $tool['slug']) }}" wire:navigate title="{{ $tool['name'] }}"
                                class="flex items-center gap-4 px-6 py-2 text-sm transition-colors border-l-2 border-transparent {{ request()->is('tool/' . $tool['slug']) ? 'text-white' : 'text-slate-400 hover:text-white' }}">
                                <!-- Indentation dot instead of full icon -->
                                <div class="w-5 flex justify-center shrink-0">
                                    <div class="w-1.5 h-1.5 rounded-full {{ request()->is('tool/' . $tool['slug']) ? 'bg-amber' : 'bg-slate-600' }}"></div>
                                </div>
                                <span class="whitespace-nowrap">{{ $tool['name'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
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
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Topbar -->
            <header class="bg-paper border-b border-hairline px-8 py-4 flex justify-between items-center shrink-0 h-[73px]">
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
                        
                        <div class="border-t border-hairline my-1"></div>
                        
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
            <main class="flex-1 overflow-y-auto bg-paper flex flex-col">
                <div class="w-full px-8 py-8">
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    @livewireScripts
</body>

</html>
