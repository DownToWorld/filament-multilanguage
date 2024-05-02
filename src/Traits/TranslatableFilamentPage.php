<?php

namespace DTW\FilamentMultilanguage\Traits;

use DTW\FilamentMultilanguage\FilamentMultilanguagePlugin;
use Illuminate\Contracts\Support\Htmlable;

trait TranslatableFilamentPage
{
    public function getBreadcrumb(): ?string
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

    public function getTitle(): string | Htmlable
    {
        $default = parent::getTitle();

        return FilamentMultilanguagePlugin::getTranslation(
            static::class,
            'title',
            $default
        ) ?? $default;
    }

    public static function getNavigationLabel(): string
    {
        $default = parent::getNavigationLabel();

        return FilamentMultilanguagePlugin::getTranslation(
            static::class,
            'navigation_label',
            $default
        ) ?? $default;
    }

    public function getSubheading(): string | Htmlable | null
    {
        $default = parent::getSubheading();

        return FilamentMultilanguagePlugin::getTranslation(
            static::class,
            'subheading',
            $default
        ) ?? $default;
    }

    public function getHeading(): string | Htmlable
    {
        $default = parent::getHeading();

        return FilamentMultilanguagePlugin::getTranslation(
            static::class,
            'heading',
            $default
        ) ?? $default;
    }
}
