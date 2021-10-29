<?php
declare(strict_types = 1);

namespace SnoerenDevelopment\LanguageSync;

use SnoerenDevelopment\LanguageSync\Commands\LanguageSync;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->commands(LanguageSync::class);
    }
}
