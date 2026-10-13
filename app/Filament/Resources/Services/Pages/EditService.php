<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Concerns\HasPublicationActions;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    use HasPublicationActions;

    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return $this->publicationActions('Service');
    }
}
