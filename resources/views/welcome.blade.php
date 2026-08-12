<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Compress, convert, done.</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data class="is-header-blur" x-bind="$store.global.documentBody">

    {{-- App preloader --}}
    <div class="app-preloader fixed z-50 grid h-full w-full place-content-center bg-slate-50 dark:bg-navy-900">
        <div class="app-preloader-inner relative inline-block size-48"></div>
    </div>

    {{-- Page Wrapper --}}
    <div id="root" class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900" x-cloak>
        
        <main class="w-full flex flex-col items-center">
            {{-- Header --}}
            <header class="w-full max-w-5xl mx-auto px-4 py-6 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-primary text-white dark:bg-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold uppercase tracking-wider text-slate-800 dark:text-navy-50">{{ config('app.name') }}</span>
                </div>
                
                <nav class="flex items-center space-x-3 sm:space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn font-medium text-slate-700 hover:text-primary dark:text-navy-100 dark:hover:text-accent">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn font-medium text-slate-700 hover:text-primary dark:text-navy-100 dark:hover:text-accent">Masuk</a>
                        <a href="{{ route('register') }}" class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90 hidden sm:inline-flex">Daftar Gratis</a>
                    @endauth
                </nav>
            </header>

            {{-- Hero --}}
            <section class="w-full max-w-4xl mx-auto px-4 py-20 lg:py-32 text-center">
                <h1 class="text-5xl lg:text-7xl font-bold tracking-tight text-slate-800 dark:text-navy-50 leading-tight mb-6">
                    Compress, <span class="text-primary dark:text-accent-light">convert,</span> done.
                </h1>
                <p class="text-lg text-slate-500 dark:text-navy-300 max-w-xl mx-auto mb-10">
                    Kumpulan perkakas simpel dan cepat untuk mengelola file Anda sehari-hari tanpa ribet.
                </p>
                
                @guest
                    <a href="{{ route('register') }}" class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90 px-8 py-3 text-lg shadow-lg shadow-primary/30">
                        Daftar Gratis Sekarang
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90 px-8 py-3 text-lg shadow-lg shadow-primary/30">
                        Buka Dashboard
                    </a>
                @endguest
            </section>

            {{-- Tools Preview --}}
            <section class="w-full bg-white dark:bg-navy-800 py-20 px-4 border-y border-slate-200 dark:border-navy-600">
                <div class="max-w-5xl mx-auto">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl font-bold text-slate-800 dark:text-navy-50 mb-4">Satu Tempat, Banyak Solusi</h2>
                        <p class="text-slate-500 dark:text-navy-300">Akses berbagai tools andalan langsung dari browser Anda.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach (config('tools') as $tool)
                            <div class="card p-6 flex flex-col items-center text-center transition-all hover:shadow-lg hover:shadow-primary/10 hover:border-primary/50 dark:hover:border-accent/50 dark:hover:shadow-accent/10 border border-slate-150 dark:border-navy-600">
                                <div class="mx-auto flex size-14 items-center justify-center rounded-xl bg-primary/10 text-primary mb-5 dark:bg-accent-light/10 dark:text-accent-light">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-slate-800 dark:text-navy-50 mb-2">{{ $tool['name'] }}</h3>
                                <p class="text-sm text-slate-500 dark:text-navy-300">{{ $tool['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Pricing Mini --}}
            <section class="w-full max-w-4xl mx-auto px-4 py-24 text-center">
                <h2 class="text-3xl font-bold text-slate-800 dark:text-navy-50 mb-4">Mulai Gratis, Upgrade Kapan Saja</h2>
                <p class="text-slate-500 dark:text-navy-300 mb-12">Pilih paket sesuai kebutuhan Anda. Paket Pro untuk akses tanpa batas.</p>
                
                <div class="flex flex-col sm:flex-row justify-center items-center gap-6 sm:gap-8">
                    {{-- Free Tier --}}
                    <div class="card w-full sm:w-64 p-8">
                        <div class="text-lg font-semibold text-slate-700 dark:text-navy-100 mb-2">Free</div>
                        <div class="text-4xl font-bold text-slate-800 dark:text-navy-50 mb-4 tracking-tight">Rp 0</div>
                        <p class="text-sm text-slate-500 dark:text-navy-300">Batasan penggunaan harian.</p>
                    </div>
                    
                    {{-- Pro Tier --}}
                    <div class="card w-full sm:w-64 p-8 border-2 border-primary dark:border-accent shadow-lg shadow-primary/20 relative">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                            <div class="badge rounded-full bg-primary text-white dark:bg-accent px-4 py-1.5 font-bold tracking-wider text-xs">
                                PRO
                            </div>
                        </div>
                        <div class="text-lg font-semibold text-primary dark:text-accent-light mb-2">Unlimited</div>
                        <div class="flex items-baseline justify-center text-slate-800 dark:text-navy-50 mb-4">
                            <span class="text-4xl font-bold tracking-tight">Rp 49K</span>
                            <span class="ml-1 text-sm text-slate-500 dark:text-navy-300 font-medium">/30hr</span>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-navy-300">Akses semua fitur tanpa batas.</p>
                    </div>
                </div>
            </section>

            {{-- Footer --}}
            <footer class="w-full border-t border-slate-200 dark:border-navy-600 bg-white dark:bg-navy-800 py-8 text-center mt-auto">
                <p class="text-sm text-slate-500 dark:text-navy-300">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </footer>
        </main>
    </div>

</body>
</html>
