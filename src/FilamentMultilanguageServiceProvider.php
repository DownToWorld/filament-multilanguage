<?php

namespace DTW\FilamentMultilanguage;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use DTW\FilamentMultilanguage\Commands\FilamentMultilanguageCommand;

class FilamentMultilanguageServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filament-multilanguage')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_filament-multilanguage_table')
            ->hasCommand(FilamentMultilanguageCommand::class);
    }
}
