<?php

namespace App\Filament\Resources\EngagementPackages\Tables;

use App\Filament\Tables\PublicationColumns;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EngagementPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('parent'))
            ->columns([
                TextColumn::make('title')->label('Package')->searchable()->sortable(),
                TextColumn::make('parent.title')->label('Builds on')->placeholder('—'),
                TextColumn::make('sort_order')->label('Order')->sortable(),
                IconColumn::make('is_selectable')->label('Selectable')->boolean(),
                ...PublicationColumns::make(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
