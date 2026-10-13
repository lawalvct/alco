<?php

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EnquiryStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Enquiries';

    /** @return list<Stat> */
    protected function getStats(): array
    {
        $new = Lead::query()->where('status', LeadStatus::New)->count();
        $unassigned = Lead::query()->open()->whereNull('assigned_to')->count();
        $overdue = Lead::query()->overdue()->count();
        $lastWeek = Lead::query()->where('created_at', '>=', now()->subDays(7))->count();

        return [
            Stat::make('New', $new)
                ->description('Awaiting first contact')
                ->color($new > 0 ? 'warning' : 'gray')
                ->url(LeadResource::getUrl('index', ['tab' => 'new'])),
            Stat::make('Unassigned', $unassigned)
                ->description('Open enquiries with no owner')
                ->color($unassigned > 0 ? 'warning' : 'gray')
                ->url(LeadResource::getUrl('index', ['tab' => 'open'])),
            Stat::make('Overdue follow-ups', $overdue)
                ->description('Follow-up date has passed')
                ->color($overdue > 0 ? 'danger' : 'gray')
                ->url(LeadResource::getUrl('index', ['tab' => 'overdue'])),
            Stat::make('Last 7 days', $lastWeek)
                ->description('Enquiries received'),
        ];
    }
}
