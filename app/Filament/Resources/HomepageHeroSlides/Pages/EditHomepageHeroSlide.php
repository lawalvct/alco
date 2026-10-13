<?php

namespace App\Filament\Resources\HomepageHeroSlides\Pages;

use App\Filament\Resources\HomepageHeroSlides\HomepageHeroSlideResource;
use App\Filament\Resources\HomepageHeroSlides\Schemas\HomepageHeroSlideForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditHomepageHeroSlide extends EditRecord
{
    protected static string $resource = HomepageHeroSlideResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return HomepageHeroSlideForm::withImageDetails($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = HomepageHeroSlideForm::saveImageDetails($data);
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewHomepage')
                ->label('View homepage')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url('/', shouldOpenInNewTab: true),
            DeleteAction::make(),
        ];
    }
}
