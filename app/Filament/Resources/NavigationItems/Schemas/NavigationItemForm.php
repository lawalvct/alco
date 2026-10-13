<?php

namespace App\Filament\Resources\NavigationItems\Schemas;

use App\Models\NavigationItem;
use App\Models\Service;
use App\Support\Navigation;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NavigationItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Link')
                    ->columns(2)
                    ->schema([
                        Select::make('location')
                            ->label('Menu')
                            ->options(NavigationItem::LOCATIONS)
                            ->default('header')
                            ->required(),
                        TextInput::make('label')
                            ->required()
                            ->maxLength(40),
                        TextInput::make('url')
                            ->label('Link to')
                            ->required()
                            ->maxLength(255)
                            ->datalist(fn (): array => self::suggestedPaths())
                            ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                if (! is_string($value) || ! Navigation::isSafeUrl($value)) {
                                    $fail('Use a site path such as /about, or a full address starting with https://.');
                                }
                            })
                            ->helperText('Pick a page from the suggestions or paste a full https:// address.')
                            ->columnSpanFull(),
                        Toggle::make('opens_in_new_tab')
                            ->label('Open in a new tab')
                            ->helperText('External https:// links always open in a new tab.'),
                        Toggle::make('is_visible')
                            ->label('Visible')
                            ->default(true),
                    ]),
            ]);
    }

    /** @return list<string> */
    private static function suggestedPaths(): array
    {
        $services = Service::published()
            ->orderBy('sort_order')
            ->pluck('slug')
            ->map(fn (string $slug): string => "/services/{$slug}")
            ->all();

        return array_values(array_merge(array_keys(Navigation::INTERNAL_PAGES), $services));
    }
}
