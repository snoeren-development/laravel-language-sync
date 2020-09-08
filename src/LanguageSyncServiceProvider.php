<?php
declare(strict_types = 1);

namespace SnoerenDevelopment\LanguageSync;

use Illuminate\Support\ServiceProvider;
use SnoerenDevelopment\LanguageSync\Commands\LanguageSync;

class LanguageSyncServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(LanguageSync::class);
    }
}
