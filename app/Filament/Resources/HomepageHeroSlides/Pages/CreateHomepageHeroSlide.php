<?php

namespace App\Filament\Resources\HomepageHeroSlides\Pages;

use App\Filament\Resources\HomepageHeroSlides\HomepageHeroSlideResource;
use App\Models\HomepageHeroSlide;
use Filament\Resources\Pages\CreateRecord;

class CreateHomepageHeroSlide extends CreateRecord
{
    protected static string $resource = HomepageHeroSlideResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = (int) HomepageHeroSlide::query()->max('sort_order') + 1;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
