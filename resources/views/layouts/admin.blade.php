<x-app-layout-sideblock is-sidebar-open="true">
    <x-slot name="title">
        @yield('title', 'Admin - ' . config('app.name'))
    </x-slot>

    <!-- Main Content Wrapper -->
    <main class="main-content w-full px-[var(--margin-x)] pb-8 pt-5 lg:pt-6">
        <div id="page-title-source" class="hidden">@yield('page_title', 'Admin Panel')</div>

        {{ $slot ?? '' }}
        @yield('content')
    </main>
</x-app-layout-sideblock>
