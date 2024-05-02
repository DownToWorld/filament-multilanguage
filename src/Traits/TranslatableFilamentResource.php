<?php

namespace DTW\FilamentMultilanguage\Traits;

use DTW\FilamentMultilanguage\FilamentMultilanguagePlugin;
use Illuminate\Contracts\Support\Htmlable;

trait TranslatableFilamentResource
{
    public static function getModelLabel(): string
    {
        $default = parent::getModelLabel();

        return FilamentMultilanguagePlugin::getTranslation(
            static::class,
            'label',
            $default
        ) ?? $default;
    }

    public static function getPluralModelLabel(): string
    {
        $default = parent::getPluralModelLabel();

        return FilamentMultilanguagePlugin::getTranslation(
            static::class,
            'plural_label',
            $default
        ) ?? $default;
    }

    public static function getBreadcrumb(): string
    {
        $default = parent::getBreadcrumb();

        return FilamentMultilanguagePlugin::getTranslation(
            static::class,
            'breadcrumb',
            $default
        ) ?? $default;
    }

    public static function getNavigationGroup(): ?string
    {
        $default = parent::getNavigationGroup();

        return FilamentMultilanguagePlugin::getTranslation(
            static::class,
            'navigation_group',
            $default
        ) ?? $default;
    }
}
