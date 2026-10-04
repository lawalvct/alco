<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('sort_order')->sortable(),
                TextColumn::make('publication_status')->badge(),
                TextColumn::make('published_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
