<?php

namespace DTW\FilamentMultilanguage\Pages;

use DTW\FilamentMultilanguage\Resources\TranslationResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use DTW\FilamentMultilanguage\FilamentMultilanguagePlugin;
use DTW\FilamentMultilanguage\Models\Translation;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\HtmlString;

class ListTranslations extends ListRecords
{
    protected static string $resource = TranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')->form([
                FileUpload::make('csv_file')->required()->acceptedFileTypes(['text/csv'])->storeFiles(false)
            ])->action(function ($data) {
                $csvFileStream = $data['csv_file']->readStream();
                $headers = fgetcsv($csvFileStream);
                while ($data = fgetcsv($csvFileStream)) {
                    $newValues = collect(array_combine($headers, $data));
                    Translation::updateOrCreate(
                        $newValues->except('translate_value')->toArray(),
                        $newValues->only('translate_value')->toArray()
                    );
                }
            }),
            Action::make('export')->action(function () {
                return response()->streamDownload(function () {
                    $out = fopen('php://output', 'w');
                    $headersLoaded = false;
                    Translation::query()
                        ->where('translate_panel_id', filament()->getCurrentPanel()->getId())
                        ->each(function (Translation $translation) use (&$out, &$headersLoaded) {
                            $data = collect($translation->toArray())->except(['id', 'created_at', 'updated_at']);
                            if (!$headersLoaded) {
                                fputcsv($out, $data->keys()->toArray());
                                $headersLoaded = true;
                            }
                            fputcsv($out, $data->values()->toArray());
                        });
                    fclose($out);
                }, 'translations.csv');
            })
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
