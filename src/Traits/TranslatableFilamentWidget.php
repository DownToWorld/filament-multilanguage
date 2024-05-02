<?php

namespace DTW\FilamentMultilanguage\Traits;

use DTW\FilamentMultilanguage\FilamentMultilanguagePlugin;
use Illuminate\Contracts\Support\Htmlable;

trait TranslatableFilamentWidget
{
    public function getHeading(): string | Htmlable | null
    {
        $default = parent::getHeading();

        return FilamentMultilanguagePlugin::getTranslation(
            static::class,
            'heading',
            $default
        ) ?? $default;
    }

    public function getDescription(): string | Htmlable | null
    {
        $default = parent::getDescription();

        return FilamentMultilanguagePlugin::getTranslation(
            static::class,
            'description',
            $default
        ) ?? $default;
    }
}
