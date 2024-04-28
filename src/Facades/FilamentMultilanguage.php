<?php

namespace DTW\FilamentMultilanguage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \DTW\FilamentMultilanguage\FilamentMultilanguage
 */
class FilamentMultilanguage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \DTW\FilamentMultilanguage\FilamentMultilanguage::class;
    }
}
