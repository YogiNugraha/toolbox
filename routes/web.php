<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Redirect root sesuai status login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

// GUEST ONLY (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
    Route::get('/register', \App\Livewire\Auth\Register::class)->name('register');
});

// AUTH ONLY (wajib login)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard\Overview::class)->name('dashboard');
    Route::get('/history', \App\Livewire\Dashboard\History::class)->name('history');
    Route::get('/profile', \App\Livewire\Dashboard\Profile::class)->name('profile');

    Route::get('/tool/{slug}', function ($slug) {
        $tools = config('tools');
        $tool = collect($tools)->firstWhere('slug', $slug);
    
        if (!$tool) {
            abort(404);
        }
    
        return view('tool_wrapper', ['tool' => $tool]);
    })->name('tool');

    Route::get('/download/{activity}', function (\App\Models\Activity $activity) {
        abort_if($activity->user_id !== auth()->id(), 403);
        abort_if(!$activity->result_path || !Storage::disk('local')->exists($activity->result_path), 404);
    
        // Construct the output filename
        $extension = pathinfo($activity->result_path, PATHINFO_EXTENSION);
        $filenameWithoutExt = pathinfo($activity->original_filename, PATHINFO_FILENAME);
        $downloadFilename = $filenameWithoutExt . '_processed.' . $extension;

        return Storage::disk('local')->download($activity->result_path, $downloadFilename);
    })->name('activity.download');

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
