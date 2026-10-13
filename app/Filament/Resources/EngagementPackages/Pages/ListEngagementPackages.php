<?php

namespace App\Filament\Resources\EngagementPackages\Pages;

use App\Filament\Resources\EngagementPackages\EngagementPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEngagementPackages extends ListRecords
{
    protected static string $resource = EngagementPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
