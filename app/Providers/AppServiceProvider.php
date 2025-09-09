<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

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
        // Name: "api" — used by throttle:api in your routes
        RateLimiter::for('api', function (Request $request) {
            return [
                // 60 requests per minute per user (or per IP if guest)
                Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()),
            ];
        });
    }
}

