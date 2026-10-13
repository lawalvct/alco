<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LeadStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case ConsultationScheduled = 'consultation_scheduled';
    case ProposalSent = 'proposal_sent';
    case Won = 'won';
    case Lost = 'lost';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Qualified => 'Qualified',
            self::ConsultationScheduled => 'Consultation scheduled',
            self::ProposalSent => 'Proposal sent',
            self::Won => 'Won',
            self::Lost => 'Lost',
            self::Closed => 'Spam / closed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::Contacted, self::Qualified, self::ConsultationScheduled, self::ProposalSent => 'info',
            self::Won => 'success',
            self::Lost, self::Closed => 'gray',
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, self::closed(), true);
    }

    /** @return list<self> */
    public static function closed(): array
    {
        return [self::Won, self::Lost, self::Closed];
    }
}
