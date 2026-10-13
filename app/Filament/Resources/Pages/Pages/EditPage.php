<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Concerns\HasPublicationActions;
use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    use HasPublicationActions;

    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return $this->publicationActions('Homepage');
    }
}
