@extends('layouts.dashboard')

@section('page_title', $tool['name'])

@section('content')
<div class="w-full">
    <!-- Breadcrumb -->
    <nav class="font-mono text-xs uppercase tracking-wider text-ink-muted flex items-center gap-2 mb-8">
        <a href="{{ route('dashboard') }}" class="hover:text-ink transition-colors">Dashboard</a>
        <span class="text-hairline">/</span>
        <span class="text-ink">{{ $tool['name'] }}</span>
    </nav>

    <!-- Tool Area -->
    <div class="w-full">
        @yield('tool_content')
    </div>
</div>
@endsection
