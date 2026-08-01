<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/tool/{slug}', function ($slug) {
    $tools = config('tools');
    $tool = collect($tools)->firstWhere('slug', $slug);

    if (!$tool) {
        abort(404);
    }

    return view('tool_wrapper', ['tool' => $tool]);
})->name('tool');
