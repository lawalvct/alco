<?php

namespace App\Filament\Resources\NavigationItems\Tables;

use App\Models\NavigationItem;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NavigationItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->description('Drag to reorder. Changes appear on the website straight away. If every link in a menu is hidden, the site shows its standard links instead.')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('label')->searchable(),
                TextColumn::make('url')
                    ->label('Link to')
                    ->description(fn (NavigationItem $record): ?string => $record->isExternal() ? 'External site' : null),
                IconColumn::make('is_visible')->label('Visible')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
