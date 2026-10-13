<?php

namespace App\Filament\Resources\Calculators;

use App\Filament\Resources\Calculators\Pages\EditCalculator;
use App\Filament\Resources\Calculators\Pages\ListCalculators;
use App\Models\Calculator;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CalculatorResource extends Resource
{
    protected static ?string $model = Calculator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Calculators';

    protected static ?string $slug = 'calculators';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('slug')->disabled()->dehydrated(false),
            TextInput::make('title')->required()->maxLength(160),
            Textarea::make('summary')->required()->maxLength(350)->rows(3),
            Textarea::make('intro')->maxLength(1000)->rows(5),
            Select::make('currency')->options(['NGN' => 'NGN', 'USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR'])->required(),
            TextInput::make('sort_order')->numeric()->integer()->minValue(0)->maxValue(999),
            Toggle::make('is_visible')->label('Visible on the public website'),
            Section::make('Search and guide content')
                ->description('Optional page-specific overrides. Empty fields use the built-in calculator explanation. Keep all claims accurate and review tax wording before publication.')
                ->schema([
                    TextInput::make('seo_title')->label('Search title')->maxLength(160),
                    Textarea::make('seo_description')->label('Search description')->maxLength(300)->rows(3),
                    Textarea::make('guide_intro')->label('Guide introduction')->maxLength(1200)->rows(4),
                    Repeater::make('guide_steps')->label('How to use steps')->schema([
                        TextInput::make('step')->required()->maxLength(250),
                    ])->maxItems(5)->addActionLabel('Add step'),
                    Textarea::make('guide_example')->label('Worked example')->maxLength(1000)->rows(4),
                    Repeater::make('guide_faqs')->label('Common questions')->schema([
                        TextInput::make('question')->required()->maxLength(200),
                        Textarea::make('answer')->required()->maxLength(600)->rows(3),
                    ])->maxItems(6)->addActionLabel('Add question'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable(),
            TextColumn::make('slug'),
            TextColumn::make('currency'),
            IconColumn::make('is_visible')->boolean(),
        ])->defaultSort('sort_order')->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCalculators::route('/'), 'edit' => EditCalculator::route('/{record}/edit')];
    }
}
