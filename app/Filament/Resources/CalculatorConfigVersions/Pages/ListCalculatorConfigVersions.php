<?php

namespace App\Filament\Resources\CalculatorConfigVersions\Pages;

use App\Filament\Resources\CalculatorConfigVersions\CalculatorConfigVersionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCalculatorConfigVersions extends ListRecords
{
    protected static string $resource = CalculatorConfigVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
