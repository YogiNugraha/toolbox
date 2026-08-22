<?php

use App\Http\Controllers\ConfirmPendingEmailController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Middleware\EnsureSingleSession;
use App\Http\Middleware\EnsureUserIsNotBanned;
use App\Http\Middleware\IsAdmin;
use App\Livewire\Admin\Plans;
use App\Livewire\Admin\Transactions;
use App\Livewire\Admin\Users;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmailNotice;
use App\Livewire\Dashboard\Billing;
use App\Livewire\Dashboard\History;
use App\Livewire\Dashboard\Invoice;
use App\Livewire\Dashboard\Overview;
use App\Livewire\Dashboard\Profile;
use App\Livewire\Admin\Overview as AdminOverview;
use App\Livewire\Pricing;
use App\Models\Activity;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

Route::get('/', function () {
    $tools = \App\Models\Tool::getActiveTools()->where('is_highlighted', true)->values();
    $totalAllTools = \App\Models\Tool::getActiveTools()->count();
    $categories = $tools->groupBy('category')->map(function ($items, $category) {
        return [
            'name' => $category,
            'count' => $items->count(),
            'slug' => \Illuminate\Support\Str::slug($category),
        ];
    })->values();
    $plans = \App\Models\Plan::where('is_active', true)->orderBy('sort_order')->get();
    return view('welcome', compact('tools', 'totalAllTools', 'categories', 'plans'));
})->name('home');

// GUEST ONLY (belum login)
Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');

    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

// WEBHOOK
Route::post('/webhook/midtrans', [MidtransWebhookController::class, 'handle'])->name('webhook.midtrans');

// AUTH ONLY (wajib login)
Route::get('/email/verify', VerifyEmailNotice::class)->middleware('auth')->name('verification.notice');

Route::get('/profile/confirm-email/{user}/{hash}', ConfirmPendingEmailController::class)
    ->middleware(['auth', 'signed'])
    ->name('profile.confirm-email');

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::find($id);

    abort_if(!$user, 404);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403);
    }

    if (!$user->hasVerifiedEmail()) {
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
    }

    if (Auth::check()) {
        return redirect()->route('email.verified');
    }

    return redirect()->route('login')->with('status', 'Email berhasil diverifikasi! Silakan login.');
})->middleware(['signed'])->name('verification.verify');

Route::get('/email/verified', function () {
    return view('livewire.auth.email-verified');
})->middleware('auth')->name('email.verified');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', EnsureUserIsNotBanned::class, EnsureSingleSession::class, 'verified', 'throttle:60,1'])->group(function () {
    Route::get('/dashboard', Overview::class)->name('dashboard');
    Route::get('/history', History::class)->name('history');
    Route::get('/profile', Profile::class)->name('profile');

    // Langganan & Pembayaran
    Route::get('/pricing', Pricing::class)->name('pricing');
    Route::get('/billing', Billing::class)->name('dashboard.billing');
    Route::get('/billing/invoice/{order_id}', Invoice::class)->name('dashboard.invoice');

    // Kategori Tools
    Route::get('/category/{category}', \App\Livewire\Dashboard\CategoryTools::class)->name('dashboard.category');

    Route::get('/tool/{slug}', function ($slug) {
        $tool = \App\Models\Tool::where('slug', $slug)->first();

        if (!$tool || !$tool->is_active) {
            abort(404);
        }

        if ($tool->is_maintenance) {
            return view('tool_maintenance', ['tool' => $tool]);
        }

        return view('tool_wrapper', ['tool' => $tool->toArray()]);
    })->name('tool');

    Route::get('/download/{activity}', function (Activity $activity) {
        abort_if($activity->user_id !== auth()->id(), 403);
        abort_if(!$activity->result_path || !Storage::disk('local')->exists($activity->result_path), 404);

        // Construct the output filename
        $extension = pathinfo($activity->result_path, PATHINFO_EXTENSION);
        $filenameWithoutExt = pathinfo($activity->original_filename, PATHINFO_FILENAME);
        $downloadFilename = $filenameWithoutExt . '_processed.' . $extension;

        return response()->download(Storage::disk('local')->path($activity->result_path), $downloadFilename);
    })->name('activity.download');
});

// ADMIN ONLY
Route::middleware(['auth', EnsureUserIsNotBanned::class, EnsureSingleSession::class, 'verified', IsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminOverview::class)->name('overview');
    Route::get('/tools', \App\Livewire\Admin\Tools::class)->name('tools');
    Route::get('/plans', Plans::class)->name('plans');
    Route::get('/users', Users::class)->name('users');
    Route::get('/transactions', Transactions::class)->name('transactions');
    Route::get('/settings', \App\Livewire\Admin\Settings::class)->name('settings');
});
