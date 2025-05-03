<?php

namespace KaziSTM\AlgeriaGeo;

use Illuminate\Support\ServiceProvider;
use KaziSTM\AlgeriaGeo\Console\InstallCommand;

class AlgeriaGeoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/Database/Migrations/' => database_path('migrations')
            ], 'algeria-geo-migrations');

            $this->publishes([
                __DIR__.'/Database/Seeders/' => database_path('seeders')
            ], 'algeria-geo-seeders');

            $this->publishes([
                __DIR__.'/../data/' => storage_path('app/algeria-geo-data')
            ], 'algeria-geo-data');

        }
    }
}