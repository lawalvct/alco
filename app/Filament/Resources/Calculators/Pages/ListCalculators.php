<?php

namespace App\Filament\Resources\Calculators\Pages;

use App\Filament\Resources\Calculators\CalculatorResource;
use Filament\Resources\Pages\ListRecords;

class ListCalculators extends ListRecords
{
    protected static string $resource = CalculatorResource::class;
}
