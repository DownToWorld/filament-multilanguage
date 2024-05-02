<?php

namespace DTW\FilamentMultilanguage\Resources;

use DTW\FilamentMultilanguage\Models\Translation;
use DTW\FilamentMultilanguage\Pages\ListTranslations;
use DTW\FilamentMultilanguage\Traits\TranslatableFilamentResource;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\TernaryFilter;

class TranslationResource extends Resource
{
    use TranslatableFilamentResource;

    protected static ?string $model = Translation::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-asia-australia';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('translate_language'),
                TextColumn::make('translate_object'),
                TextColumn::make('translate_key'),
                TextInputColumn::make('translate_value')->grow()
                    ->extraInputAttributes(['size' => 500])
                    ->placeholder(fn ($record) => $record->translate_default),
            ])
            ->filters([
                SelectFilter::make('language')
                    ->options(
                        Translation::query()
                            ->where('translate_panel_id', filament()->getCurrentPanel()->getId())
                            ->pluck('translate_language')
                            ->unique()
                            ->mapWithKeys(fn (String $language) => [$language => ucfirst($language)])
                    )
                    ->attribute('translate_language'),
                SelectFilter::make('object')
                    ->options(
                        Translation::query()
                            ->where('translate_panel_id', filament()->getCurrentPanel()->getId())
                            ->pluck('translate_object')
                            ->unique()
                            ->mapWithKeys(fn (String $objectName) => [$objectName => ucfirst($objectName)])
                    )
                    ->attribute('translate_object'),
                TernaryFilter::make('translate_default')
                    ->label('Default values')
                    ->default(false)
                    ->placeholder('All translations')
                    ->trueLabel('Without default values')
                    ->falseLabel('With default values')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('translate_default'),
                        false: fn (Builder $query) => $query->whereNotNull('translate_default'),
                        blank: fn (Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                    ),
                TernaryFilter::make('translate_value')
                    ->label('Translated')
                    ->placeholder('All translations')
                    ->trueLabel('Translated values')
                    ->falseLabel('Not translated values')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('translate_value'),
                        false: fn (Builder $query) => $query->whereNull('translate_value'),
                        blank: fn (Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                    )
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTranslations::route('/')
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('translate_panel_id', filament()->getCurrentPanel()->getId());
    }
}
