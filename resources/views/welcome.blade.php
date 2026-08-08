<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Compress, convert, done.</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-paper text-ink font-sans antialiased min-h-screen flex flex-col">

    <!-- Header -->
    <header class="border-b border-hairline bg-white">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="font-display font-bold text-xl tracking-tight">{{ config('app.name') }}</div>
            <nav class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium hover:text-amber transition-colors">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium hover:text-amber transition-colors px-3 py-1.5">Masuk</a>
                    <a href="{{ route('register') }}" class="text-sm font-medium bg-white border border-hairline hover:border-amber rounded-sm px-4 py-1.5 transition-colors">Daftar Gratis</a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Hero -->
    <main class="flex-grow">
        <section class="py-20 lg:py-32 px-4 text-center max-w-3xl mx-auto">
            <h1 class="font-display font-bold text-5xl lg:text-7xl mb-6 tracking-tight text-ink leading-tight">Compress, convert, done.</h1>
            <p class="text-lg text-ink-muted mb-10 max-w-xl mx-auto">Kumpulan perkakas simpel dan cepat untuk mengelola file Anda sehari-hari tanpa ribet.</p>
            @guest
                <a href="{{ route('register') }}" class="inline-block bg-amber text-ink font-bold px-8 py-4 rounded-sm shadow-sm hover:bg-amber/90 transition-colors text-lg">Daftar Gratis Sekarang</a>
            @else
                <a href="{{ route('dashboard') }}" class="inline-block bg-amber text-ink font-bold px-8 py-4 rounded-sm shadow-sm hover:bg-amber/90 transition-colors text-lg">Buka Dashboard</a>
            @endguest
        </section>

        <!-- Tools Preview -->
        <section class="py-16 bg-white border-y border-hairline px-4">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="font-display font-bold text-3xl mb-3">Satu Tempat, Banyak Solusi</h2>
                    <p class="text-ink-muted">Akses berbagai tools andalan langsung dari browser Anda.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach (config('tools') as $tool)
                        <div class="border border-hairline rounded-sm p-6 hover:border-amber transition-colors bg-paper/50 flex flex-col items-center text-center">
                            <div class="w-12 h-12 bg-white border border-hairline rounded-sm flex items-center justify-center mb-4 text-amber">
                                <!-- Icon placeholder (Heroicons simplified) -->
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <h3 class="font-display font-bold text-lg mb-2">{{ $tool['name'] }}</h3>
                            <p class="text-sm text-ink-muted">{{ $tool['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Pricing Mini -->
        <section class="py-20 px-4 max-w-3xl mx-auto text-center">
            <h2 class="font-display font-bold text-3xl mb-4">Mulai Gratis, Upgrade Kapan Saja</h2>
            <p class="text-ink-muted mb-8">Pilih paket sesuai kebutuhan Anda. Paket Pro untuk akses tanpa batas.</p>
            <div class="flex flex-col sm:flex-row justify-center items-center gap-6">
                <div class="border border-hairline bg-white p-6 rounded-sm w-full sm:w-64">
                    <div class="font-bold mb-2">Free</div>
                    <div class="font-mono text-2xl mb-4">Rp 0</div>
                    <p class="text-xs text-ink-muted">Batasan penggunaan harian.</p>
                </div>
                <div class="border border-amber bg-white p-6 rounded-sm w-full sm:w-64 relative shadow-sm">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber text-xs font-bold px-3 py-1">PRO</div>
                    <div class="font-bold mb-2">Unlimited</div>
                    <div class="font-mono text-2xl mb-4">Rp 49K<span class="text-xs text-ink-muted font-sans">/30hr</span></div>
                    <p class="text-xs text-ink-muted">Akses semua fitur tanpa batas.</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="border-t border-hairline py-8 bg-white text-center">
        <p class="font-mono text-xs text-slate-500">&copy; {{ date('Y') }} {{ config('app.name') }}.</p>
    </footer>

</body>
</html>
