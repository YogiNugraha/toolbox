<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php
        $siteName = \App\Models\Setting::get('site_name', \App\Models\Setting::get('brand_name', config('app.name')));
        $siteFavicon = \App\Models\Setting::get('site_favicon');
        $siteDescription = \App\Models\Setting::get('site_description');
        $announcementEnabled = filter_var(\App\Models\Setting::get('announcement_enabled', false), FILTER_VALIDATE_BOOLEAN);
        $announcementText = \App\Models\Setting::get('announcement_text');
        $announcementType = \App\Models\Setting::get('announcement_type', 'primary');
    @endphp

    <meta charset="UTF-8">

    <link rel="icon" type="image/png" href="{{ $siteFavicon ? Storage::url($siteFavicon) : asset('favicon.png') }}" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="{{ $siteDescription ?: 'Solusi perkakas digital instan untuk mengolah, mengompres, dan mengonversi file Anda setiap hari tanpa instalasi software.' }}">
    
    <title>{{ $siteName }} @isset($title)
            - {{ $title }}
        @endisset
    </title>

    <!-- CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <script>
        /**
         * THIS SCRIPT REQUIRED FOR PREVENT FLICKERING IN SOME BROWSERS
         */
        localStorage.getItem("_x_darkMode_on") === "true" &&
            document.documentElement.classList.add("dark");
    </script>

    @isset($head)
        {{ $head }}
    @endisset
    @livewireStyles

</head>

<body x-data x-bind="$store.global.documentBody"
    class="@isset($isSidebarOpen) {{ $isSidebarOpen === 'true' ? 'is-sidebar-open' : '' }} @endisset @isset($isHeaderBlur) {{ $isHeaderBlur === 'true' ? 'is-header-blur' : '' }} @endisset @isset($hasMinSidebar) {{ $hasMinSidebar === 'true' ? 'has-min-sidebar' : '' }} @endisset @isset($headerSticky) {{ $headerSticky === 'false' ? 'is-header-not-sticky' : '' }} @endisset">

    <!-- App preloader-->
    <x-app-preloader></x-app-preloader>

    {{-- Global Announcement Bar --}}
    @if ($announcementEnabled && !empty($announcementText))
        @php
            $bgClass = match($announcementType) {
                'info' => 'bg-info text-white',
                'warning' => 'bg-warning text-slate-900',
                'success' => 'bg-success text-white',
                default => 'bg-primary text-white dark:bg-accent',
            };
        @endphp
        <div x-data="{ show: true }" x-show="show" class="{{ $bgClass }} relative z-50 px-4 py-2 text-center text-xs font-semibold shadow-sm transition-all">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="w-full text-center flex items-center justify-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                    <span>{{ $announcementText }}</span>
                </div>
                <button @click="show = false" class="ml-4 shrink-0 opacity-75 hover:opacity-100 p-0.5 rounded focus:outline-none" aria-label="Close Announcement">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Page Wrapper -->
    <div id="root" class="min-h-100vh w-full flex grow bg-slate-50 dark:bg-navy-900" x-cloak>

        {{ $slot }}

    </div>

    <!--
  This is a place for Alpine.js Teleport feature
  @see https://alpinejs.dev/directives/teleport
-->
    <div id="x-teleport-target"></div>

    <!-- Alpine is started automatically by Livewire v3 -->

    @isset($script)
        {{ $script }}
    @endisset
    
    @livewireScripts
</body>

</html>
