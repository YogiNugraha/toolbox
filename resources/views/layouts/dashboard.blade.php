<x-app-layout-sideblock is-sidebar-open="true">
    <x-slot name="title">
        @yield('title', 'Dashboard - ' . config('app.name'))
    </x-slot>

    <!-- Main Content Wrapper -->
    <main class="main-content w-full px-[var(--margin-x)] pb-8 pt-5 lg:pt-6">
        <div id="page-title-source" class="hidden">@yield('page_title', 'Dashboard')</div>
        @if(View::hasSection('page_breadcrumb'))
        <div class="flex items-center space-x-4 pb-5 lg:pb-6">
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
                @if(View::hasSection('breadcrumb_parent'))
                    @yield('breadcrumb_parent')
                @endif
                <li class="text-slate-600 dark:text-navy-200">@yield('page_breadcrumb')</li>
            </ul>
        </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>
</x-app-layout-sideblock>
