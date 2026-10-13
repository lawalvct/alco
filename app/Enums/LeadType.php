<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LeadType: string implements HasColor, HasLabel
{
    case Contact = 'contact';
    case Consultation = 'consultation';

    public function getLabel(): string
    {
        return match ($this) {
            self::Contact => 'Contact enquiry',
            self::Consultation => 'Consultation request',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Contact => 'gray',
            self::Consultation => 'primary',
        };
    }
}
