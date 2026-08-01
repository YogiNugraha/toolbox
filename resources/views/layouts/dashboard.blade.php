<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard - ToolBox')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-indigo-900 text-white flex flex-col h-full shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-indigo-800">
            <a href="{{ route('dashboard') }}" class="text-2xl font-black flex items-center gap-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a1 1 0 01-1-1v-3a1 1 0 011-1h1a2 2 0 100-4H4a1 1 0 01-1-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                </svg>
                ToolBox
            </a>
        </div>
        
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-700' }}">Dashboard</a>
            <a href="{{ route('history') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('history') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-700' }}">Riwayat</a>
            
            <div class="pt-4 pb-2">
                <p class="px-3 text-xs font-semibold text-indigo-300 uppercase tracking-wider">Tools</p>
            </div>
            @foreach(config('tools') as $tool)
                <a href="{{ route('tool', $tool['slug']) }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->is('tool/'.$tool['slug']) ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-700' }}">{{ $tool['name'] }}</a>
            @endforeach
        </nav>
        
        <div class="p-4 border-t border-indigo-800">
            <a href="{{ route('profile') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('profile') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-700' }}">Profil Saya</a>
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="w-full text-left block px-3 py-2 rounded-md text-sm font-medium text-indigo-100 hover:bg-indigo-700">Logout</button>
            </form>
        </div>
    </aside>

    <!-- Main content area -->
    <div class="flex-1 flex flex-col h-full overflow-hidden">
        
        <!-- Topbar -->
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 border-b border-gray-200 shrink-0">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">@yield('page_title', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">Halo, <strong>{{ auth()->user()->name }}</strong></span>
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold border border-indigo-200">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
            <div class="max-w-6xl mx-auto">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
