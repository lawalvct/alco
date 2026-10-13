<?php

namespace App\Filament\Resources\TopicHubs\Pages;

use App\Filament\Concerns\HasPublicationActions;
use App\Filament\Resources\TopicHubs\TopicHubResource;
use Filament\Resources\Pages\EditRecord;

class EditTopicHub extends EditRecord
{
    use HasPublicationActions;

    protected static string $resource = TopicHubResource::class;

    protected function getHeaderActions(): array
    {
        return $this->publicationActions('Topic');
    }
}
