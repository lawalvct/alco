<?php

namespace App\Filament\Resources\EngagementPackages\Schemas;

use App\Models\EngagementPackage;
use App\Models\Service;
use Closure;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class EngagementPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Package identity')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Package name')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('slug')
                            ->required()
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit')
                            ->helperText('Used in links such as /book-consultation?package=growth. Locked after creation.'),
                        Select::make('parent_id')
                            ->label('Builds on')
                            ->relationship(
                                'parent',
                                'title',
                                fn (Builder $query, ?EngagementPackage $record) => $query->when(
                                    $record,
                                    fn (Builder $query) => $query->whereKeyNot($record?->getKey()),
                                ),
                            )
                            ->helperText('Its inclusions appear as “Everything in …, plus”.')
                            ->rule(fn (?EngagementPackage $record): Closure => self::noLoopRule($record)),
                        TextInput::make('sort_order')
                            ->label('Display order')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(999)
                            ->default(0),
                        Toggle::make('is_selectable')
                            ->label('Visitors can choose this package')
                            ->default(true)
                            ->helperText('Applies immediately. Turn off to keep the package listed but stop new selections.')
                            ->columnSpanFull(),
                    ]),
                Group::make()
                    ->statePath('draft_data')
                    ->schema([
                        Section::make('Draft package content')
                            ->description('Save first; visitors see changes only after you publish the saved draft. Leave out prices, “per month” or subscription wording until ALCO approves pricing and terms.')
                            ->schema([
                                TextInput::make('summary')
                                    ->label('One-line summary')
                                    ->required()
                                    ->maxLength(120),
                                Textarea::make('audience')
                                    ->label('Best for')
                                    ->required()
                                    ->rows(3)
                                    ->maxLength(400),
                                Repeater::make('inclusions')
                                    ->label('What’s included')
                                    ->helperText('List only what this package adds. Inherited inclusions are shown automatically.')
                                    ->schema([
                                        TextInput::make('label')->hiddenLabel()->required()->maxLength(120),
                                    ])
                                    ->minItems(1)
                                    ->maxItems(12)
                                    ->default([])
                                    ->addActionLabel('Add inclusion'),
                                Select::make('service_ids')
                                    ->label('Related services')
                                    ->multiple()
                                    ->options(fn (): array => Service::query()->orderBy('sort_order')->pluck('title', 'id')->all())
                                    ->helperText('The package is suggested on these service pages.'),
                            ]),
                    ]),
            ]);
    }

    private static function noLoopRule(?EngagementPackage $record): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($record): void {
            if (! $record || ! is_numeric($value)) {
                return;
            }

            $seen = [];
            $current = EngagementPackage::find((int) $value);

            while ($current && ! isset($seen[$current->id])) {
                if ($current->is($record)) {
                    $fail('A package cannot build on itself, directly or through another package.');

                    return;
                }

                $seen[$current->id] = true;
                $current = $current->parent_id ? EngagementPackage::find($current->parent_id) : null;
            }
        };
    }
}
