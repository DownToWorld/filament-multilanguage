<?php

namespace DTW\FilamentMultilanguage;

use DTW\FilamentMultilanguage\Models\Translation;
use DTW\FilamentMultilanguage\Resources\TranslationResource;
use Filament\Actions\StaticAction;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Htmlable;
use Closure;
use Filament\Actions\ActionGroup as BaseActionGroup;
use Filament\Forms\Components\Field;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Cache;

class FilamentMultilanguagePlugin implements Plugin
{
    protected static array $cachedTranslations = [];
    protected static String $translationsCacheKey = 'filament_multilanguage_translations';

    public function getId(): string
    {
        return 'filament-multilanguage';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            TranslationResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        $this->translateMakeableNavigationItems();
        $this->translateMakeableTables();
        $this->translateMakeableColumns();
        $this->translateMakeableFields();
        $this->translateMakeableActions();
        $this->translateMakeableActionGroups();
    }

    public static function getTranslation(String $namespace, String $translatable, ?String $default = null): ?String
    {
        $panel = filament()->getCurrentPanel();

        if (!isset(static::$cachedTranslations[$panel->getId()]) || empty(static::$cachedTranslations[$panel->getId()])) {
            $cachedTranslations = Cache::get(static::$translationsCacheKey . '_' . $panel->getId());

            if (!$cachedTranslations) {
                $databaseTranslations = Translation::query()
                    ->where('translate_panel_id', $panel->getId())
                    ->get()
                    ->groupBy('translate_language')
                    ->each
                    ->groupBy('translate_object')
                    ->each
                    ->mapWithKeys(fn (Translation $translation) => [$translation->translate_key => $translation->translate_value])
                    ->toArray();

                Cache::put(static::$translationsCacheKey . '_' . $panel->getId(), $databaseTranslations);

                static::$cachedTranslations[$panel->getId()] = $databaseTranslations;
            } else {
                static::$cachedTranslations[$panel->getId()] = $cachedTranslations;
            }
        }

        $isTranslationInCache = isset(static::$cachedTranslations[$panel->getId()][App::currentLocale()][$namespace][$translatable]);

        if ($isTranslationInCache) {
            return static::$cachedTranslations[$panel->getId()][App::currentLocale()][$namespace][$translatable];
        }

        $newTranslations = collect(config('filament-multilanguage.languages'))->mapWithKeys(
            fn (String $language) =>
            [$language => Translation::updateOrCreate([
                'translate_panel_id' => $panel->getId(),
                'translate_object' => Str::afterLast($namespace, '\\'),
                'translate_key' => $translatable,
                'translate_language' => $language,
            ], ['translate_default' => $default])->translate_value]
        )->toArray();

        return $newTranslations[App::currentLocale()];
    }

    public static function flushCachedTranslations(): void
    {
        static::$cachedTranslations = [];
        Cache::forget(static::$translationsCacheKey);
    }

    public function translateMakeableNavigationItems(): void
    {
        App::bind(NavigationItem::class, function ($app, $params) {
            return new class($params['label']) extends NavigationItem
            {
                public function getLabel(): string
                {
                    $default = $this->evaluate($this->label);
                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        'NavigationItem',
                        Str::snake($this->label) . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });
    }

    protected function translateMakeableTables(): void
    {
        App::bind(Table::class, function ($app, $params) {
            return new class($params['livewire']) extends Table
            {
                //
            };
        });
    }

    protected function translateMakeableColumns(): void
    {
        App::bind(TextColumn::class, function ($app, $params) {
            return new class($params['name']) extends TextColumn
            {
                public function getDescriptionAbove(): string | Htmlable | null
                {
                    $default = $this->evaluate($this->descriptionAbove);

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_description_above',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getDescriptionBelow(): string | Htmlable | null
                {
                    $default = $this->evaluate($this->descriptionBelow);

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_description_below',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getLabel(): string | Htmlable
                {
                    $default = $this->evaluate($this->label) ??
                        (string) str($this->getName())
                            ->beforeLast('.')
                            ->afterLast('.')
                            ->kebab()
                            ->replace(['-', '_'], ' ')
                            ->ucfirst();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $this->shouldTranslateLabel ? __($default) : $default;
                }
            };
        });

        // [...] REPEAT WITH REST OF TRANSLATABLE COLUMNS
    }

    protected function translateMakeableFields(): void
    {
        App::bind(Field::class, function ($app, $params) {
            return new class($params['name']) extends Field
            {
                //
            };
        });
    }

    protected function translateMakeableActions(): void
    {
        App::bind(StaticAction::class, function ($app, $params) {
            return new class($params['name'] ?? null) extends StaticAction
            {
                //
            };
        });
    }

    protected function translateMakeableActionGroups(): void
    {
        App::bind(BaseActionGroup::class, function ($app, $params) {
            return new class($params['actions']) extends BaseActionGroup
            {
                //
            };
        });
    }
}
