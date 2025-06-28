<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

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
        ResetPassword::createUrlUsing(function ($notifiable, $token) {
            // Tambahkan log untuk debug
            Log::info('ResetPassword request', [
                'email' => $notifiable->email ?? null,
                'role'  => $notifiable->role ?? null,
                'token' => $token
            ]);

            // Handle jika role tidak tersedia (fallback)
            $role = $notifiable->role ?? null;

            if ($role === 'driver') {
                return 'http://localhost:8100/reset-password?token=' . $token . '&email=' . urlencode($notifiable->email);
            }

            // Default ke halaman Laravel
            return URL::route('password.reset', [
                'token' => $token,
                'email' => $notifiable->email,
            ], false);
        });
    }
}
