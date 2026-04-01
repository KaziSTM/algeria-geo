<?php

namespace KaziSTM\AlgeriaGeo;

use KaziSTM\AlgeriaGeo\Console\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AlgeriaGeoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('algeria-geo')
            ->hasMigrations([
                'create_cities_table',
                'create_communes_table',
            ])
            ->hasCommand(InstallCommand::class)
            ->publishesServiceProvider('algeria-geo')
            ->hasAssets()
            ->hasConfigFile();
    }
}