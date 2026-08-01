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
                <a href="{{ route('dashboard') }}" class="font-display font-bold text-white text-lg tracking-tight flex items-center gap-3 overflow-hidden whitespace-nowrap">
                    <x-lucide-package class="w-7 h-7 text-white/70 shrink-0 -ml-0.5" />
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms>ToolBox</span>
                </a>
            </div>

            <nav class="flex-1 py-4 space-y-1 overflow-y-auto overflow-x-hidden scrollbar-hide">
                <a href="{{ route('dashboard') }}" title="Dashboard"
                    class="flex items-center gap-4 px-6 py-3 text-sm transition-colors border-l-2 {{ request()->routeIs('dashboard') ? 'text-white font-medium bg-white/5 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent' }}">
                    <x-lucide-layout-dashboard class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Dashboard</span>
                </a>
                
                <a href="{{ route('history') }}" title="Riwayat"
                    class="flex items-center gap-4 px-6 py-3 text-sm transition-colors border-l-2 {{ request()->routeIs('history') ? 'text-white font-medium bg-white/5 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent' }}">
                    <x-lucide-history class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Riwayat</span>
                </a>

                <p x-show="sidebarOpen" x-transition.opacity.duration.300ms
                    class="px-6 pt-6 pb-2 text-[11px] font-mono uppercase tracking-widest text-slate-500 border-t border-white/10 mt-4 mb-2 whitespace-nowrap">
                    Tools
                </p>
                <div x-show="!sidebarOpen" class="w-8 h-px bg-white/10 mx-auto mt-6 mb-4"></div>

                @foreach (config('tools') as $tool)
                    <a href="{{ route('tool', $tool['slug']) }}" title="{{ $tool['name'] }}"
                        class="flex items-center gap-4 px-6 py-3 text-sm transition-colors border-l-2 {{ request()->is('tool/' . $tool['slug']) ? 'text-white font-medium bg-white/5 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent' }}">
                        <x-lucide-wrench class="w-5 h-5 shrink-0" />
                        <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">{{ $tool['name'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="py-4 border-t border-white/10 shrink-0 overflow-hidden">
                <a href="{{ route('profile') }}" title="Profil Saya"
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
                <div class="flex items-center gap-3">
                    <span class="text-sm text-ink-muted">Halo, <span class="font-medium text-ink">{{ auth()->user()->name }}</span></span>
                    <div class="w-8 h-8 rounded-full bg-amber/15 border border-amber/40 text-amber font-mono text-sm flex items-center justify-center">
                        {{ substr(auth()->user()->name, 0, 1) }}
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
