<x-app-layout>
    <x-slot name="title">
        @yield('title', 'Dashboard - ' . config('app.name'))
    </x-slot>

    <!-- Main Content Wrapper -->
    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        <div class="flex items-center space-x-4 py-5 lg:py-6">
            <div>
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                    @yield('page_title', 'Dashboard')
                </h2>
                @hasSection('page_description')
                    <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">
                        @yield('page_description')
                    </p>
                @endif
            </div>

            @hasSection('page_breadcrumb')
            <div class="hidden h-full py-1 sm:flex">
                <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
            </div>
            <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('dashboard') }}">Dashboard</a>
                    <svg x-ignore xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li>@yield('page_breadcrumb')</li>
            </ul>
            @endif
        </div>

        {{ $slot ?? '' }}
        @yield('content')
    </main>
</x-app-layout>
