<?php

namespace App\Filament\Resources\HomepageHeroSlides\Pages;

use App\Filament\Resources\HomepageHeroSlides\HomepageHeroSlideResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListHomepageHeroSlides extends ListRecords
{
    protected static string $resource = HomepageHeroSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewHomepage')
                ->label('View homepage')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url('/', shouldOpenInNewTab: true),
            CreateAction::make()->label('Add slide'),
        ];
    }
}
