<?php

namespace App\Filament\Resources\EngagementPackages;

use App\Filament\Resources\EngagementPackages\Pages\CreateEngagementPackage;
use App\Filament\Resources\EngagementPackages\Pages\EditEngagementPackage;
use App\Filament\Resources\EngagementPackages\Pages\ListEngagementPackages;
use App\Filament\Resources\EngagementPackages\Schemas\EngagementPackageForm;
use App\Filament\Resources\EngagementPackages\Tables\EngagementPackagesTable;
use App\Models\EngagementPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EngagementPackageResource extends Resource
{
    protected static ?string $model = EngagementPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquare3Stack3d;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Packages';

    protected static ?string $modelLabel = 'engagement package';

    protected static ?string $slug = 'packages';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return EngagementPackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EngagementPackagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEngagementPackages::route('/'),
            'create' => CreateEngagementPackage::route('/create'),
            'edit' => EditEngagementPackage::route('/{record}/edit'),
        ];
    }
}
