<?php

namespace App\Providers;

use App\Mail\ResendHttpTransport;
use Illuminate\Support\Facades\Mail;
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
        // Resend HTTPS taşıyıcısı (SMTP portları kapalı olduğu için API ile gönderim)
        Mail::extend('resend-http', fn (array $config) => new ResendHttpTransport((string) config('services.resend.key')));
    }
}
