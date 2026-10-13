<?php

namespace App\Filament\Resources\TopicHubs\Pages;

use App\Filament\Resources\TopicHubs\TopicHubResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTopicHubs extends ListRecords
{
    protected static string $resource = TopicHubResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
