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
                    <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">Reset Password</h2>
                    <p class="text-slate-400 dark:text-navy-300">Buat password baru untuk akun Anda</p>
                </div>
            </div>

            <form class="mt-10" wire:submit="resetPassword">
                <div>
                    <label class="relative flex">
                        <input wire:model="email" class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900" placeholder="Email" type="email" required autofocus />
                        <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </span>
                    </label>
                    @error('email') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                </div>
                <div class="mt-4">
                    <label class="relative flex">
                        <input wire:model="password" class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900" placeholder="Password Baru" type="password" required />
                        <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </span>
                    </label>
                    @error('password') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                </div>
                <div class="mt-4">
                    <label class="relative flex">
                        <input wire:model="password_confirmation" class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900" placeholder="Konfirmasi Password" type="password" required />
                        <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                        </span>
                    </label>
                </div>
                <button type="submit" class="btn mt-10 h-10 w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    Reset Password
                </button>
            </form>
        </div>
        <div class="my-5 flex justify-center text-xs text-slate-400 dark:text-navy-300">
            <span>{{ $footerCopyright }}</span>
        </div>
    </main>
</div>
