<?php

namespace DTW\FilamentMultilanguage;

use DTW\FilamentMultilanguage\Models\Translation;
use DTW\FilamentMultilanguage\Observers\TranslationObserver;
use DTW\FilamentMultilanguage\Policies\TranslationPolicy;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Illuminate\Support\Facades\Gate;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;

class FilamentMultilanguageServiceProvider extends PackageServiceProvider
{
    public function bootingPackage()
    {
        Translation::observe(TranslationObserver::class);
        Gate::policy(Translation::class, TranslationPolicy::class);

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales(config('filament-multilanguage.languages') ?? ['en', 'es', 'fr']);
        });
    }

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
            ->hasMigration('create_filament_multilanguage_table');
    }
}
