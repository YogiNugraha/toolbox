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
            <img class="w-full" x-show="!$store.global.isDarkModeEnabled" src="{{ asset('images/illustrations/dashboard-check.svg') }}" alt="image" />
            <img class="w-full" x-show="$store.global.isDarkModeEnabled" src="{{ asset('images/illustrations/dashboard-check-dark.svg') }}" alt="image" />
        </div>
    </div>
    <main class="flex w-full flex-col items-center bg-white dark:bg-navy-700 lg:max-w-md" wire:poll="checkVerification">
        <div class="flex w-full max-w-sm grow flex-col justify-center p-5">
            <div class="text-center">
                @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                    <div class="mx-auto flex size-14 shrink-0 items-center justify-center lg:hidden">
                        <img class="size-full object-contain" src="{{ \Illuminate\Support\Facades\Storage::url($siteLogo) }}" alt="{{ $siteName }}" />
                    </div>
                @else
                    <img class="mx-auto size-14 lg:hidden" src="{{ asset('images/app-logo.svg') }}" alt="logo" />
                @endif
                <div class="mt-4">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-warning/10">
                        <svg class="size-8 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <h2 class="mt-4 text-2xl font-semibold text-slate-600 dark:text-navy-100">Verifikasi Email</h2>
                    <p class="mt-2 text-slate-400 dark:text-navy-300">
                        Kami telah mengirim link verifikasi ke <strong class="text-slate-600 dark:text-navy-100">{{ auth()->user()->email }}</strong>
                    </p>
                </div>
            </div>

            <div class="mt-8 text-center text-sm text-slate-400 dark:text-navy-300">
                Jika Anda tidak menerima email tersebut, kami akan dengan senang hati mengirimkan yang baru.
            </div>

            <div class="mt-6 flex flex-col space-y-3"
                x-data="{ cooldown: 0 }"
                x-on:cooldown-start.window="cooldown = $event.detail.seconds; let t = setInterval(() => { cooldown--; if (cooldown <= 0) clearInterval(t); }, 1000);">
                <button wire:click="resend" type="button" :disabled="cooldown > 0"
                    class="btn h-10 w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="resend" x-show="cooldown === 0">Kirim Ulang Email Verifikasi</span>
                    <span wire:loading wire:target="resend">Mengirim...</span>
                    <span wire:loading.remove wire:target="resend" x-show="cooldown > 0" x-text="'Terkirim — Kirim Ulang (' + cooldown + 's)'" style="display: none;"></span>
                </button>

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit"
                        class="btn h-10 w-full border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
        <div class="my-5 flex justify-center text-xs text-slate-400 dark:text-navy-300">
            <span>{{ $footerCopyright }}</span>
        </div>
    </main>
</div>
