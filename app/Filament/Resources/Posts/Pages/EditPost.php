<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Concerns\HasPublicationActions;
use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    use HasPublicationActions;

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return $this->publicationActions('Article');
    }
}
