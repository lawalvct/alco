<?php

namespace App\Filament\Resources\HomepageHeroSlides\Pages;

use App\Filament\Resources\HomepageHeroSlides\HomepageHeroSlideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHomepageHeroSlides extends ListRecords
{
    protected static string $resource = HomepageHeroSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
