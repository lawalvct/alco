<?php

namespace App\Filament\Resources\TopicHubs\Tables;

use App\Filament\Tables\PublicationColumns;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TopicHubsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(),
            TextColumn::make('slug')->fontFamily('mono'),
            TextColumn::make('sort_order')->label('Order')->sortable(),
            ...PublicationColumns::make(),
        ])->defaultSort('sort_order')->recordActions([EditAction::make()]);
    }
}
