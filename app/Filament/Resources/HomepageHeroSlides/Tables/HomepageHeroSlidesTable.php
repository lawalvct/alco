<?php

namespace App\Filament\Resources\HomepageHeroSlides\Tables;

use App\Models\HomepageHeroSlide;
use App\Support\HeroImageSpec;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HomepageHeroSlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['mediaAsset', 'mobileMediaAsset']))
            ->description(sprintf(
                'Drag to reorder. Visitors see visible slides in this order, up to %d.',
                HeroImageSpec::MAX_VISIBLE_SLIDES,
            ))
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('mediaAsset.path')
                    ->label('Image')
                    ->disk('public')
                    ->imageHeight(56),
                TextColumn::make('mediaAsset.title')
                    ->label('Main image')
                    ->description(fn (HomepageHeroSlide $record): ?string => $record->mobileMediaAsset
                        ? "Phone image: {$record->mobileMediaAsset->title}"
                        : null),
                TextColumn::make('focus')
                    ->state(fn (HomepageHeroSlide $record): string => "{$record->focal_x}% · {$record->focal_y}%"),
                IconColumn::make('is_decorative')
                    ->label('Decorative')
                    ->boolean(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('No slides yet')
            ->emptyStateDescription('Until a slide is visible, the homepage shows the standard ALCO banner image.');
    }
}
