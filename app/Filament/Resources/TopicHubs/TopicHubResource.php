<?php

namespace App\Filament\Resources\TopicHubs;

use App\Filament\Resources\TopicHubs\Pages\CreateTopicHub;
use App\Filament\Resources\TopicHubs\Pages\EditTopicHub;
use App\Filament\Resources\TopicHubs\Pages\ListTopicHubs;
use App\Filament\Resources\TopicHubs\Schemas\TopicHubForm;
use App\Filament\Resources\TopicHubs\Tables\TopicHubsTable;
use App\Models\TopicHub;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TopicHubResource extends Resource
{
    protected static ?string $model = TopicHub::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static string|UnitEnum|null $navigationGroup = 'Website';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Insight topics';
    protected static ?string $modelLabel = 'insight topic';
    protected static ?string $slug = 'insight-topics';
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema { return TopicHubForm::configure($schema); }
    public static function table(Table $table): Table { return TopicHubsTable::configure($table); }

    public static function getPages(): array
    {
        return [
            'index' => ListTopicHubs::route('/'),
            'create' => CreateTopicHub::route('/create'),
            'edit' => EditTopicHub::route('/{record}/edit'),
        ];
    }
}
