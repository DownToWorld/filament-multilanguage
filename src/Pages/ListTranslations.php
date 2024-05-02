<?php

namespace DTW\FilamentMultilanguage\Pages;

use DTW\FilamentMultilanguage\Resources\TranslationResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use DTW\FilamentMultilanguage\FilamentMultilanguagePlugin;
use DTW\FilamentMultilanguage\Traits\Translatable;
use DTW\FilamentMultilanguage\Traits\TranslatableFilamentPage;
use Illuminate\Support\HtmlString;

class ListTranslations extends ListRecords
{
    use TranslatableFilamentPage;

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

    public function getSubheading(): string | Htmlable
    {
        $missingTraitComponents = FilamentMultilanguagePlugin::getNonTranslatedComponentsList();

        $componentsHtmlList = collect($missingTraitComponents)->map(function ($componentClass) {
            return "<li>- $componentClass</li>";
        })->join("");

        if (!empty($missingTraitComponents)) {
            return new HtmlString("<ul>$componentsHtmlList</ul> " . __('filament-multilanguage::translations.missing_components'));
        }

        return null;
    }
}
