<?php

namespace App\Filament\Resources\TeamMembers\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TeamMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('portrait'))
            ->description('Drag to reorder. Published members appear on the About page in this order.')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('portrait.path')
                    ->label('Portrait')
                    ->disk('public')
                    ->circular()
                    ->imageHeight(40),
                TextColumn::make('name')->searchable(),
                TextColumn::make('role')->placeholder('Not confirmed'),
                IconColumn::make('is_published')->label('Published')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
