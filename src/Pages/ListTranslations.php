<?php

namespace DTW\FilamentMultilanguage\Pages;

use DTW\FilamentMultilanguage\Resources\TranslationResource;
use Filament\Resources\Pages\ListRecords;

class ListTranslations extends ListRecords
{
    protected static string $resource = TranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50];
    }
}
