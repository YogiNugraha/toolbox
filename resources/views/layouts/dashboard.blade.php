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

<body class="bg-paper text-ink font-sans antialiased flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-ink text-slate-300 flex flex-col h-full shrink-0">
        <div class="px-6 py-5 border-b border-white/10 shrink-0">
            <a href="{{ route('dashboard') }}"
                class="font-display font-bold text-white text-lg tracking-tight flex items-center gap-2">
                <svg class="w-5 h-5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                    </path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                ToolBox
            </a>
        </div>

        <nav class="flex-1 py-4 space-y-0.5 overflow-y-auto">
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-6 py-2.5 text-sm {{ request()->routeIs('dashboard') ? 'text-white font-medium bg-white/5 border-l-2 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 transition-colors border-l-2 border-transparent' }}">Dashboard</a>
            <a href="{{ route('history') }}"
                class="flex items-center gap-3 px-6 py-2.5 text-sm {{ request()->routeIs('history') ? 'text-white font-medium bg-white/5 border-l-2 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 transition-colors border-l-2 border-transparent' }}">Riwayat</a>

            <p
                class="px-6 pt-6 pb-2 text-[11px] font-mono uppercase tracking-widest text-slate-500 border-t border-white/10 mt-4 mb-2">
                Tools
            </p>

            @foreach (config('tools') as $tool)
                <a href="{{ route('tool', $tool['slug']) }}"
                    class="flex items-center gap-3 px-6 py-2.5 text-sm {{ request()->is('tool/' . $tool['slug']) ? 'text-white font-medium bg-white/5 border-l-2 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 transition-colors border-l-2 border-transparent' }}">{{ $tool['name'] }}</a>
            @endforeach
        </nav>

        <div class="py-4 border-t border-white/10 shrink-0">
            <a href="{{ route('profile') }}"
                class="flex items-center gap-3 px-6 py-2.5 text-sm {{ request()->routeIs('profile') ? 'text-white font-medium bg-white/5 border-l-2 border-amber' : 'text-slate-400 hover:text-white hover:bg-white/5 transition-colors border-l-2 border-transparent' }}">Profil
                Saya</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full text-left flex items-center gap-3 px-6 py-2.5 text-sm text-slate-400 hover:text-white hover:bg-white/5 transition-colors border-l-2 border-transparent">Logout</button>
            </form>
        </div>
    </aside>

    <!-- Main content area -->
    <div class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Topbar -->
        <header class="bg-paper border-b border-hairline px-8 py-4 flex justify-between items-center shrink-0">
            <div>
                <h1 class="font-display font-bold text-xl text-ink">@yield('page_title', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-ink-muted">Halo, <span
                        class="font-medium text-ink">{{ auth()->user()->name }}</span></span>
                <div
                    class="w-8 h-8 rounded-full bg-amber/15 border border-amber/40 text-amber font-mono text-sm flex items-center justify-center">
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

    @livewireScripts
</body>

</html>
