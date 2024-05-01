<?php

namespace DTW\FilamentMultilanguage;

use DTW\FilamentMultilanguage\Models\Translation;
use DTW\FilamentMultilanguage\Resources\TranslationResource;
use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Actions\ActionGroup as BaseActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action as FormsAction;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Cache;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Notifications\Actions\Action as NotificationsAction;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Widgets\Widget;
use Livewire\ComponentHook;
use function Livewire\{invade, on, off, once};
use Filament\Navigation\NavigationItem;

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
        $this->translateDiscoverableFilamentComponents();
        $this->translateMakeableTables();
        $this->translateMakeableColumns();
        $this->translateMakeableFields();
        $this->translateMakeableActions();
        $this->translateMakeableActionGroups();
        $this->translateMakeableFilters();
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

    protected function translateDiscoverableFilamentComponents(): void
    {
        $componentHook = new class extends ComponentHook
        {
            static function provide()
            {
                $renderHook = function (...$params) {
                    [$component, $view, $params] = $params;

                    if ($component instanceof Page) {
                        $page = invade($component);
                    }

                    if ($component instanceof Widget) {
                        $widget = invade($component);
                    }
                };

                on('render', $renderHook);
            }
        };

        app('livewire')->componentHook($componentHook);
    }

    protected function translateMakeableNavigationItems(): void
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
                public function getModelLabel(): string
                {
                    $default = parent::getModelLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class . '_table',
                        'model_label',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getPluralModelLabel(): string
                {
                    $default = parent::getPluralModelLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class . '_table',
                        'model_label_plural',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getRecordTitle(Model $record): string
                {
                    $default = parent::getRecordTitle($record);

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class . '_table',
                        'record_title',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getHeading(): string | Htmlable | null
                {
                    $default = parent::getHeading();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class . '_table',
                        'heading',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getDescription(): string | Htmlable | null
                {
                    $default = parent::getDescription();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class . '_table',
                        'description',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getEmptyStateDescription(): string | Htmlable | null
                {
                    $default = parent::getEmptyStateDescription();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class . '_table',
                        'empty_state_description',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getEmptyStateHeading(): string | Htmlable
                {
                    $default = parent::getEmptyStateHeading();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class . '_table',
                        'empty_state_heading',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });
    }

    protected function translateMakeableColumns(): void
    {
        /*
            CheckboxColumn
            ColorColumn
            ColumnGroup
            IconColumn
            ImageColumn
            SelectColumn
            TextColumn
            TextInputColumn
            ToggleColumn
            ViewColumn
        */

        App::bind(ViewColumn::class, function ($app, $params) {
            return new class($params['name']) extends ViewColumn
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });

        App::bind(ToggleColumn::class, function ($app, $params) {
            return new class($params['name']) extends ToggleColumn
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });

        App::bind(TextInputColumn::class, function ($app, $params) {
            return new class($params['name']) extends TextInputColumn
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });

        App::bind(SelectColumn::class, function ($app, $params) {
            return new class($params['name']) extends SelectColumn
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getOptions(): array
                {
                    $options = parent::getOptions();

                    return collect($options)->map(function ($default, $defaultKey) {
                        $translation = FilamentMultilanguagePlugin::getTranslation(
                            $this->getLivewire()::class,
                            'column_' . $this->name . '_option_' . $defaultKey,
                            $default
                        );

                        return $translation ?? $default;
                    })->toArray();
                }
            };
        });

        App::bind(ImageColumn::class, function ($app, $params) {
            return new class($params['name']) extends ImageColumn
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });

        App::bind(IconColumn::class, function ($app, $params) {
            return new class($params['name']) extends IconColumn
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });

        App::bind(ColumnGroup::class, function ($app, $params) {
            return new class($params['label'], $params['columns']) extends ColumnGroup
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_group_' . $this->label . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });

        App::bind(CheckboxColumn::class, function ($app, $params) {
            return new class($params['name']) extends CheckboxColumn
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });

        App::bind(ColorColumn::class, function ($app, $params) {
            return new class($params['name']) extends ColorColumn
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });

        App::bind(TextColumn::class, function ($app, $params) {
            return new class($params['name']) extends TextColumn
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getDescriptionAbove(): string | Htmlable | null
                {
                    $default = parent::getDescriptionAbove();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_description_above',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getDescriptionBelow(): string | Htmlable | null
                {
                    $default = parent::getDescriptionBelow();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'column_' . $this->name . '_description_below',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });
    }

    protected function translateMakeableFields(): void
    {
        App::bind(TextInput::class, function ($app, $params) {
            return new class($params['name']) extends TextInput
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'input_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getHint(): string | Htmlable | null
                {
                    $default = parent::getHint();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'input_' . $this->name . '_hint',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getHelperText(): string | Htmlable | null
                {
                    $default = parent::getHelperText();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'input_' . $this->name . '_helper_text',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });

        // [...] Add rest of input classes
    }

    protected function translateMakeableActions(): void
    {
        App::bind(Action::class, function ($app, $params) {
            return new class($params['name'] ?? null) extends Action
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'action_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getTooltip(): ?string
                {
                    $default = parent::getTooltip();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'action_' . $this->name . '_tooltip',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });

        App::bind(FormsAction::class, function ($app, $params) {
            return new class($params['name'] ?? null) extends FormsAction
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'action_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getTooltip(): ?string
                {
                    $default = parent::getTooltip();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'action_' . $this->name . '_tooltip',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });

        App::bind(NotificationsAction::class, function ($app, $params) {
            return new class($params['name'] ?? null) extends NotificationsAction
            {
                public function getLabel(): string | Htmlable
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'action_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getTooltip(): ?string
                {
                    $default = parent::getTooltip();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'action_' . $this->name . '_tooltip',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });
    }

    protected function translateMakeableActionGroups(): void
    {
        App::bind(BaseActionGroup::class, function ($app, $params) {
            return new class($params['actions']) extends BaseActionGroup
            {
                public function getLabel(): string
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'group_action_' . $this->label . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getTooltip(): ?string
                {
                    $default = parent::getTooltip();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'group_action_' . $this->label . '_tooltip',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });
    }

    protected function translateMakeableFilters(): void
    {
        App::bind(SelectFilter::class, function ($app, $params) {
            return new class($params['name'] ?? null) extends SelectFilter
            {
                public function getLabel(): string
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'filter_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getOptions(): array
                {
                    $options = parent::getOptions();

                    return collect($options)->map(function ($default, $defaultKey) {
                        $translation = FilamentMultilanguagePlugin::getTranslation(
                            $this->getLivewire()::class,
                            'filter_' . $this->name . '_option_' . $defaultKey,
                            $default
                        );

                        return $translation ?? $default;
                    })->toArray();
                }
            };
        });

        App::bind(TernaryFilter::class, function ($app, $params) {
            return new class($params['name'] ?? null) extends TernaryFilter
            {
                public function getLabel(): string
                {
                    $default = parent::getLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'filter_' . $this->name . '_label',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getTrueLabel(): ?string
                {
                    $default = parent::getTrueLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'filter_' . $this->name . '_true_label',
                        $default
                    );

                    return $translation ?? $default;
                }

                public function getFalseLabel(): ?string
                {
                    $default = parent::getFalseLabel();

                    $translation = FilamentMultilanguagePlugin::getTranslation(
                        $this->getLivewire()::class,
                        'filter_' . $this->name . '_false_label',
                        $default
                    );

                    return $translation ?? $default;
                }
            };
        });
    }
}
