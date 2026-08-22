@extends('layouts.dashboard')

@php
    $catName = $tool['category'] ?? 'Tools';
    $catSlug = \Illuminate\Support\Str::slug($catName);
    $displayName = $catName;
    if (strtolower($catName) === 'image') $displayName = 'Gambar & Foto';
    if (strtolower($catName) === 'document') $displayName = 'Dokumen & PDF';
@endphp

@section('page_title', $tool['name'])

@section('breadcrumb_parent')
    <li class="flex items-center space-x-2">
        <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('dashboard.category', $catSlug) }}">{{ $displayName }}</a>
        <svg x-ignore xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </li>
@endsection

@section('page_breadcrumb', $tool['name'])

@section('content')
<div class="w-full">
    <!-- Tool Area -->
    <div class="w-full">
        @yield('tool_content')
    </div>
</div>
@endsection
