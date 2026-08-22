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
            <p class="text-xl font-bold uppercase tracking-wider text-slate-700 dark:text-navy-100">
                {{ $siteName }}
            </p>
        </a>
    </div>
    <div class="hidden w-full place-items-center lg:grid">
        <div class="w-full max-w-lg p-6">
            <img class="w-full" x-show="!$store.global.isDarkModeEnabled"
                src="{{ asset('images/illustrations/dashboard-check.svg') }}" alt="image" />
            <img class="w-full" x-show="$store.global.isDarkModeEnabled"
                src="{{ asset('images/illustrations/dashboard-check-dark.svg') }}" alt="image" />
        </div>
    </div>
    <main class="flex w-full flex-col items-center bg-white dark:bg-navy-700 lg:max-w-md">
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
                    <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">
                        Lupa Password?
                    </h2>
                    <p class="text-slate-400 dark:text-navy-300">
                        Masukkan email Anda untuk menerima link reset
                    </p>
                </div>
            </div>

            <form class="mt-10" wire:submit="sendResetLink"
                x-data="{ cooldown: 0 }"
                x-on:cooldown-start.window="cooldown = $event.detail.seconds; let t = setInterval(() => { cooldown--; if (cooldown <= 0) clearInterval(t); }, 1000);">
                <div>
                    <label class="relative flex">
                        <input wire:model="email"
                            class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                            placeholder="Email terdaftar" type="email" required />
                        <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </span>
                    </label>
                    @error('email')
                        <span class="text-tiny-plus text-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit"
                    x-bind:disabled="cooldown > 0"
                    class="btn mt-10 h-10 w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="sendResetLink" x-show="cooldown === 0">Kirim Link Reset</span>
                    <span wire:loading wire:target="sendResetLink">Mengirim...</span>
                    <span wire:loading.remove wire:target="sendResetLink" x-show="cooldown > 0" x-text="'Terkirim — Kirim Ulang (' + cooldown + 's)'" style="display: none;"></span>
                </button>
            </form>

            <div class="mt-4 text-center text-xs-plus">
                <p class="line-clamp-1">
                    <span>Ingat password Anda?</span>
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                        href="{{ route('login') }}">Masuk</a>
                </p>
            </div>
        </div>
        <div class="my-5 flex justify-center text-xs text-slate-400 dark:text-navy-300">
            <span>{{ $footerCopyright }}</span>
        </div>
    </main>
</div>
