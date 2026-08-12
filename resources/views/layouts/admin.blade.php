<x-app-layout>
    <x-slot name="title">
        @yield('title', 'Admin - ' . config('app.name'))
    </x-slot>

    <!-- Main Content Wrapper -->
    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        <div class="flex items-center space-x-4 py-5 lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                @yield('page_title', 'Admin Panel')
            </h2>
        </div>

        {{ $slot ?? '' }}
        @yield('content')
    </main>
</x-app-layout>
