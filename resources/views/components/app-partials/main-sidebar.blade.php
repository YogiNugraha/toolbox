<div class="main-sidebar">
    <div
        class="flex h-full w-full flex-col items-center border-r border-slate-150 bg-white dark:border-navy-700 dark:bg-navy-800">
        <!-- Application Logo -->
        <div class="flex pt-4">
            <a href="/">
                <img class="size-11 transition-transform duration-500 ease-in-out hover:rotate-[360deg]"
                    src="{{ asset('images/app-logo.svg') }}" alt="logo" />
            </a>
        </div>

        <!-- Main Sections Links -->
        <div class="is-scrollbar-hidden flex grow flex-col space-y-4 overflow-y-auto pt-6">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
                class="flex size-11 items-center justify-center rounded-lg outline-hidden transition-colors duration-200 {{ request()->routeIs('dashboard') || request()->is('tool/*') || request()->is('category/*') ? 'text-primary bg-primary/10 dark:bg-navy-600 dark:text-accent-light' : 'hover:bg-primary/20 dark:hover:bg-navy-300/20' }}"
                x-tooltip.placement.right="'Dashboard'">
                <svg class="size-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
            </a>

            <!-- History -->
            <a href="{{ route('history') }}"
                class="flex size-11 items-center justify-center rounded-lg outline-hidden transition-colors duration-200 {{ request()->routeIs('history') ? 'text-primary bg-primary/10 dark:bg-navy-600 dark:text-accent-light' : 'hover:bg-primary/20 dark:hover:bg-navy-300/20' }}"
                x-tooltip.placement.right="'Riwayat'">
                <svg class="size-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </a>

            <!-- Billing -->
            <a href="{{ route('dashboard.billing') }}"
                class="flex size-11 items-center justify-center rounded-lg outline-hidden transition-colors duration-200 {{ request()->routeIs('dashboard.billing') || request()->routeIs('dashboard.invoice') ? 'text-primary bg-primary/10 dark:bg-navy-600 dark:text-accent-light' : 'hover:bg-primary/20 dark:hover:bg-navy-300/20' }}"
                x-tooltip.placement.right="'Billing & Paket'">
                <svg class="size-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </a>
            
            @if(auth()->check() && auth()->user()->is_admin)
            <!-- Admin Panel -->
            <a href="{{ route('admin.overview') }}"
                class="flex size-11 items-center justify-center rounded-lg outline-hidden transition-colors duration-200 {{ request()->is('admin*') ? 'text-primary bg-primary/10 dark:bg-navy-600 dark:text-accent-light' : 'hover:bg-primary/20 dark:hover:bg-navy-300/20' }}"
                x-tooltip.placement.right="'Admin Panel'">
                <svg class="size-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </a>
            @endif
        </div>

        <!-- Bottom Links -->
        <div class="flex flex-col items-center space-y-3 py-3">
            <!-- Profile -->
            <div x-data="usePopper({ placement: 'right-end', offset: 12 })" @click.outside="if(isShowPopper) isShowPopper = false" class="flex">
                <button @click="isShowPopper = !isShowPopper" x-ref="popperRef" class="avatar size-12 cursor-pointer">
                    @if(auth()->check() && auth()->user()->profile_photo_path)
                        <img class="rounded-full" src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="avatar" />
                    @else
                        <div class="rounded-full bg-slate-200 dark:bg-navy-500 w-full h-full flex items-center justify-center font-bold text-slate-500 dark:text-navy-100">
                            {{ auth()->check() ? substr(auth()->user()->name, 0, 1) : 'U' }}
                        </div>
                    @endif
                    <span class="absolute right-0 size-3.5 rounded-full border-2 border-white bg-success dark:border-navy-700"></span>
                </button>
                <div :class="isShowPopper && 'show'" class="popper-root fixed" x-ref="popperRoot">
                    <div class="popper-box w-64 rounded-lg border border-slate-150 bg-white shadow-soft dark:border-navy-600 dark:bg-navy-700">
                        <div class="flex items-center space-x-4 rounded-t-lg bg-slate-100 py-5 px-4 dark:bg-navy-800">
                            <div class="avatar size-14">
                                @if(auth()->check() && auth()->user()->profile_photo_path)
                                    <img class="rounded-full" src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="avatar" />
                                @else
                                    <div class="rounded-full bg-slate-200 dark:bg-navy-500 w-full h-full flex items-center justify-center font-bold text-slate-500 dark:text-navy-100">
                                        {{ auth()->check() ? substr(auth()->user()->name, 0, 1) : 'U' }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('profile') }}" class="text-base font-medium text-slate-700 hover:text-primary focus:text-primary dark:text-navy-100 dark:hover:text-accent-light dark:focus:text-accent-light">
                                    {{ auth()->check() ? auth()->user()->name : 'User' }}
                                </a>
                                <p class="text-xs text-slate-400 dark:text-navy-300">
                                    {{ auth()->check() ? auth()->user()->email : '' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col pt-2 pb-5">
                            <a href="{{ route('profile') }}" class="group flex items-center space-x-3 py-2 px-4 tracking-wide outline-hidden transition-all hover:bg-slate-100 focus:bg-slate-100 dark:hover:bg-navy-600 dark:focus:bg-navy-600">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-warning text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="font-medium text-slate-700 transition-colors group-hover:text-primary group-focus:text-primary dark:text-navy-100 dark:group-hover:text-accent-light dark:group-focus:text-accent-light">Profile</h2>
                                    <div class="text-xs text-slate-400 line-clamp-1 dark:text-navy-300">Pengaturan profil Anda</div>
                                </div>
                            </a>
                            <div class="mt-3 px-4">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn h-9 w-full space-x-2 bg-primary text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
