<?php

namespace DTW\FilamentMultilanguage\Observers;

use DTW\FilamentMultilanguage\FilamentMultilanguagePlugin;
use DTW\FilamentMultilanguage\Models\Translation;
use Illuminate\Filesystem\Filesystem;

class TranslationObserver
{
    /**
     * Handle the Translation "created" event.
     */
    public function created(Translation $translation): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Translation "updated" event.
     */
    public function updated(Translation $translation): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Translation "deleted" event.
     */
    public function deleted(Translation $translation): void
    {
        //
    }

    /**
     * Handle the Translation "restored" event.
     */
    public function restored(Translation $translation): void
    {
        // ...
    }

    /**
     * Handle the Translation "forceDeleted" event.
     */
    public function forceDeleted(Translation $translation): void
    {
        // ...
    }

    private function clearCache()
    {
        FilamentMultilanguagePlugin::flushCachedTranslations();
        app(Filesystem::class)->deleteDirectory((config('filament.cache_path') ?? base_path('bootstrap/cache/filament')) . DIRECTORY_SEPARATOR . 'panels');
    }
}
