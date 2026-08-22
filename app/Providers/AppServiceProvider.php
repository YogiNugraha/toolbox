<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::defaultView('components.lineone-pagination');
        \Carbon\Carbon::setLocale('id');

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $brandName = \App\Models\Setting::get('brand_name');
                if ($brandName) {
                    config(['app.name' => $brandName]);
                }
            }
        } catch (\Exception $e) {
            // Abaikan jika database belum siap
        }
    }
}
