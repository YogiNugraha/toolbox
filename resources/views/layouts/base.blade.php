<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-paper text-ink font-sans antialiased min-h-screen">
    {{ $slot ?? '' }}
    @yield('content')
    
    @livewireScripts
</body>
</html>
