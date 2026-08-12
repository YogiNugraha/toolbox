@extends('layouts.dashboard')

@section('page_title', $tool['name'])

@section('page_breadcrumb', $tool['name'])

@section('content')
<div class="w-full">
    <!-- Tool Area -->
    <div class="w-full">
        @yield('tool_content')
    </div>
</div>
@endsection
