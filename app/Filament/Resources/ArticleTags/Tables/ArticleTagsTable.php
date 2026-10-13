<?php

namespace App\Filament\Resources\ArticleTags\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArticleTagsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('slug')->fontFamily('mono'),
        ])->defaultSort('name')->recordActions([EditAction::make()]);
    }
}
