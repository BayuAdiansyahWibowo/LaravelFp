<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });

    $this->routes(function () {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    });

    // Tambahan custom reset password URL untuk driver
    \Illuminate\Auth\Notifications\ResetPassword::createUrlUsing(function ($notifiable, $token) {
        if ($notifiable->role === 'driver') {
            return 'http://localhost:8100/reset-password?token=' . $token . '&email=' . urlencode($notifiable->email);
        }

        // Default Laravel (untuk admin dan lainnya)
        return url(route('password.reset', [
            'token' => $token,
            'email' => $notifiable->email,
        ], false));
    });
}

}