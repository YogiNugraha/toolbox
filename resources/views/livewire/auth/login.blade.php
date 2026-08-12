<div class="flex w-full grow">
    <div class="fixed top-0 hidden p-6 lg:block lg:px-12">
        <a href="/" class="flex items-center space-x-2">
            <img class="size-12" src="{{ asset('images/app-logo.svg') }}" alt="logo" />
            <p class="text-xl font-semibold uppercase text-slate-700 dark:text-navy-100">
                {{ config('app.name') }}
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
                <img class="mx-auto size-16 lg:hidden" src="{{ asset('images/app-logo.svg') }}" alt="logo" />
                <div class="mt-4">
                    <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">
                        Selamat Datang
                    </h2>
                    <p class="text-slate-400 dark:text-navy-300">
                        Silakan masuk untuk melanjutkan
                    </p>
                </div>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-lg bg-success/10 px-4 py-3 text-sm text-success">
                    {{ session('status') }}
                </div>
            @endif

            <form class="mt-10" wire:submit="login">
                <div>
                    <label class="relative flex">
                        <input wire:model="email"
                            class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                            placeholder="Email" type="email" required />
                        <span
                            class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </span>
                    </label>
                    @error('email')
                        <span class="text-tiny-plus text-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mt-4">
                    <label class="relative flex">
                        <input wire:model="password"
                            class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                            placeholder="Password" type="password" required />
                        <span
                            class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                    </label>
                    @error('password')
                        <span class="text-tiny-plus text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mt-4 flex items-center justify-between space-x-2">
                    <label class="inline-flex items-center space-x-2">
                        <input wire:model="remember"
                            class="form-checkbox is-outline size-5 rounded-sm border-slate-400/70 bg-slate-100 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:before:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent"
                            type="checkbox" />
                        <span class="line-clamp-1">Ingat Saya</span>
                    </label>
                    <a href="{{ route('password.request') }}"
                        class="text-xs text-slate-400 transition-colors line-clamp-1 hover:text-slate-800 focus:text-slate-800 dark:text-navy-300 dark:hover:text-navy-100 dark:focus:text-navy-100">Lupa
                        Password?</a>
                </div>
                <button type="submit"
                    class="btn mt-10 h-10 w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    Masuk
                </button>
                <div class="mt-4 text-center text-xs-plus">
                    <p class="line-clamp-1">
                        <span>Belum punya akun?</span>
                        <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                            href="{{ route('register') }}">Daftar sekarang</a>
                    </p>
                </div>
            </form>
        </div>
        <div class="my-5 flex justify-center text-xs text-slate-400 dark:text-navy-300">
            <span>&copy; {{ date('Y') }} {{ config('app.name') }}</span>
        </div>
    </main>
</div>
