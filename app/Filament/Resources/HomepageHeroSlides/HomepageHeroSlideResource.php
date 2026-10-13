<?php

namespace App\Filament\Resources\HomepageHeroSlides;

use App\Filament\Resources\HomepageHeroSlides\Pages\CreateHomepageHeroSlide;
use App\Filament\Resources\HomepageHeroSlides\Pages\EditHomepageHeroSlide;
use App\Filament\Resources\HomepageHeroSlides\Pages\ListHomepageHeroSlides;
use App\Filament\Resources\HomepageHeroSlides\Schemas\HomepageHeroSlideForm;
use App\Filament\Resources\HomepageHeroSlides\Tables\HomepageHeroSlidesTable;
use App\Models\HomepageHeroSlide;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HomepageHeroSlideResource extends Resource
{
    protected static ?string $model = HomepageHeroSlide::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Homepage slider';

    protected static ?string $modelLabel = 'slide';

    protected static ?string $pluralModelLabel = 'homepage slider';

    protected static ?string $slug = 'homepage-slider';

    public static function form(Schema $schema): Schema
    {
        return HomepageHeroSlideForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HomepageHeroSlidesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHomepageHeroSlides::route('/'),
            'create' => CreateHomepageHeroSlide::route('/create'),
            'edit' => EditHomepageHeroSlide::route('/{record}/edit'),
        ];
    }
}
