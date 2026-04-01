<?php

namespace KaziSTM\AlgeriaGeo\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use KaziSTM\AlgeriaGeo\AlgeriaGeoServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            AlgeriaGeoServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/Migrations');
    }
}