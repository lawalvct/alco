<?php

namespace App\Filament\Resources\HomepageHeroSlides\Pages;

use App\Filament\Resources\HomepageHeroSlides\HomepageHeroSlideResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHomepageHeroSlide extends EditRecord
{
    protected static string $resource = HomepageHeroSlideResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
