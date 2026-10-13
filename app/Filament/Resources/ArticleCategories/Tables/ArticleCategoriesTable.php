<?php

namespace App\Filament\Resources\ArticleCategories\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArticleCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('slug')->fontFamily('mono'),
                TextColumn::make('sort_order')->label('Order')->sortable(),
                IconColumn::make('is_visible')->label('Visible')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()]);
    }
}
