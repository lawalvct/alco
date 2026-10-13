<?php

namespace App\Filament\Resources\CalculatorConfigVersions;

use App\Filament\Resources\CalculatorConfigVersions\Pages\CreateCalculatorConfigVersion;
use App\Filament\Resources\CalculatorConfigVersions\Pages\EditCalculatorConfigVersion;
use App\Filament\Resources\CalculatorConfigVersions\Pages\ListCalculatorConfigVersions;
use App\Models\CalculatorConfigVersion;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CalculatorConfigVersionResource extends Resource
{
    protected static ?string $model = CalculatorConfigVersion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 12;

    protected static ?string $navigationLabel = 'Tax calculator rules';

    protected static ?string $slug = 'tax-calculator-rules';

    protected static ?string $modelLabel = 'tax calculator rule';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tool_slug')->label('Tool')->options(['vat' => 'VAT', 'withholding-tax' => 'Withholding tax'])->required(),
            TextInput::make('scope_key')->required()->default('standard')->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->maxLength(120)->helperText('Exact transaction / recipient classification key used by the calculator.'),
            TextInput::make('version')->numeric()->integer()->minValue(1)->required()->default(1),
            TextInput::make('label')->required()->maxLength(160),
            TextInput::make('rate_percent')->label('Rate (%)')->numeric()->minValue(0)->maxValue(100)->required()->helperText('Enter a percentage, e.g. 10 means 10%. No statutory rate is prefilled.'),
            DatePicker::make('effective_from')->required()->native(false),
            DatePicker::make('effective_to')->native(false)->afterOrEqual('effective_from'),
            TextInput::make('reviewer_name')->label('Qualified reviewer')->maxLength(120),
            DatePicker::make('reviewed_at')->native(false),
            TextInput::make('source_url')->url()->startsWith(['https://'])->maxLength(500),
            Textarea::make('notes')->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('tool_slug')->label('Tool'),
            TextColumn::make('scope_key')->label('Rule key'),
            TextColumn::make('version'),
            TextColumn::make('rate_percent')->suffix('%'),
            TextColumn::make('effective_from')->date(),
            TextColumn::make('effective_to')->date()->placeholder('Open ended'),
            TextColumn::make('status')->badge()->color(fn (string $state): string => $state === 'approved' ? 'success' : 'gray'),
        ])->defaultSort('created_at', 'desc')->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCalculatorConfigVersions::route('/'),
            'create' => CreateCalculatorConfigVersion::route('/create'),
            'edit' => EditCalculatorConfigVersion::route('/{record}/edit'),
        ];
    }
}
