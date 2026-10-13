<?php

namespace App\Filament\Resources\EngagementPackages\Pages;

use App\Filament\Concerns\HasPublicationActions;
use App\Filament\Resources\EngagementPackages\EngagementPackageResource;
use Filament\Resources\Pages\EditRecord;

class EditEngagementPackage extends EditRecord
{
    use HasPublicationActions;

    protected static string $resource = EngagementPackageResource::class;

    protected function getHeaderActions(): array
    {
        return $this->publicationActions('Package');
    }
}
