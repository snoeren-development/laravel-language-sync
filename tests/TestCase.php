<?php
declare(strict_types = 1);

namespace SnoerenDevelopment\LanguageSync\Tests;

use SnoerenDevelopment\LanguageSync\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application $app The container object.
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }
}
