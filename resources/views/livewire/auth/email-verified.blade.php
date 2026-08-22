<div class="flex w-full grow">
    @php
        $siteName = \App\Models\Setting::get('site_name', \App\Models\Setting::get('brand_name', config('app.name')));
        $siteLogo = \App\Models\Setting::get('site_logo');
        $footerCopyright = \App\Models\Setting::get('footer_copyright', '© ' . date('Y') . ' ' . $siteName . '. All rights reserved.');
    @endphp

    <div class="fixed top-0 hidden p-6 lg:block lg:px-12">
        <a href="/" class="flex items-center space-x-3">
            @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                <div class="flex size-10 shrink-0 items-center justify-center">
                    <img class="size-full object-contain" src="{{ \Illuminate\Support\Facades\Storage::url($siteLogo) }}" alt="{{ $siteName }}" />
                </div>
            @else
                <img class="size-10 transition-transform duration-500 ease-in-out hover:rotate-[360deg]" src="{{ asset('images/app-logo.svg') }}" alt="logo" />
            @endif
            <p class="text-xl font-bold uppercase tracking-wider text-slate-700 dark:text-navy-100">{{ $siteName }}</p>
        </a>
    </div>

    <div class="hidden w-full place-items-center lg:grid">
        <div class="w-full max-w-lg p-6">
            <img class="w-full" src="{{ asset('images/illustrations/dashboard-check.svg') }}" alt="image" />
        </div>
    </div>
    <main class="flex w-full flex-col items-center bg-white dark:bg-navy-700 lg:max-w-md">
        <div class="flex w-full max-w-sm grow flex-col items-center justify-center p-5"
            x-data="{ seconds: 5 }"
            x-init="
                const t = setInterval(() => {
                    seconds--;
                    if (seconds <= 0) { clearInterval(t); window.location.href = '{{ route('dashboard') }}'; }
                }, 1000);
            ">
            <div class="text-center">
                @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                    <div class="mx-auto mb-4 flex size-14 shrink-0 items-center justify-center lg:hidden">
                        <img class="size-full object-contain" src="{{ \Illuminate\Support\Facades\Storage::url($siteLogo) }}" alt="{{ $siteName }}" />
                    </div>
                @endif
                <div class="mx-auto flex size-20 items-center justify-center rounded-full bg-success/10">
                    <svg class="size-10 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="mt-5 text-2xl font-semibold text-slate-600 dark:text-navy-100">Verifikasi Berhasil!</h2>
                <p class="mt-2 text-slate-400 dark:text-navy-300">
                    Email kamu sudah terverifikasi. Diarahkan otomatis dalam
                    <span x-text="seconds" class="font-semibold text-slate-600 dark:text-navy-100"></span>
                    detik.
                </p>
                <a href="{{ route('dashboard') }}"
                    class="btn mt-8 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    Ke Dashboard Sekarang
                </a>
            </div>
        </div>
        <div class="my-5 flex justify-center text-xs text-slate-400 dark:text-navy-300">
            <span>{{ $footerCopyright }}</span>
        </div>
    </main>
</div>
