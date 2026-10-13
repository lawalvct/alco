<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Filament\Tables\PublicationColumns;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('categories'))
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->wrap(),
                TextColumn::make('review_status')->label('Editorial review')->badge()->color(fn (string $state): string => match ($state) {
                    'approved' => 'success',
                    'in_review' => 'warning',
                    default => 'gray',
                }),
                TextColumn::make('categories.name')->label('Categories')->badge()->separator(', '),
                ...PublicationColumns::make(),
            ])
            ->filters([
                SelectFilter::make('review_status')->label('Editorial review')->options([
                    'draft' => 'Draft',
                    'in_review' => 'In review',
                    'approved' => 'Approved for publication',
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([EditAction::make()]);
    }
}
