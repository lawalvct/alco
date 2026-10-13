<?php

namespace App\Filament\Resources\MediaAssets\Tables;

use App\Models\MediaAsset;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class MediaAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount(['heroSlides', 'mobileHeroSlides', 'teamPortraits']))
            ->columns([
                ImageColumn::make('path')
                    ->label('Preview')
                    ->disk('public')
                    ->imageHeight(48),
                TextColumn::make('title')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (MediaAsset $record): ?string => $record->alt_text ? null : 'No alt text'),
                TextColumn::make('dimensions')
                    ->state(fn (MediaAsset $record): string => $record->dimensionsLabel()),
                TextColumn::make('size')
                    ->formatStateUsing(fn (?int $state): string => $state ? Number::fileSize($state, precision: 1) : '—')
                    ->sortable(),
                IconColumn::make('rights_confirmed')
                    ->label('Rights')
                    ->boolean(),
                TextColumn::make('usage')
                    ->label('In use')
                    ->state(fn (MediaAsset $record): int => (int) $record->getAttribute('hero_slides_count')
                        + (int) $record->getAttribute('mobile_hero_slides_count')
                        + (int) $record->getAttribute('team_portraits_count'))
                    ->formatStateUsing(fn (int $state): string => $state === 0 ? 'No' : "{$state}×"),
                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->date('j M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('rights_confirmed')->label('Usage rights confirmed'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateDescription('Upload images here, or directly from the homepage slider and team screens.');
    }
}
