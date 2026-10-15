<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Filament\Tables\PublicationColumns;
use App\Models\Page;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('slug')
                    ->label('Public address')
                    ->state(fn (Page $record): string => $record->publicPath())
                    ->url(fn (Page $record): ?string => $record->publication_status === 'published' ? $record->publicPath() : null)
                    ->openUrlInNewTab()
                    ->searchable(),
                ...PublicationColumns::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
