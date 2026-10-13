<?php

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/** Open enquiries that are new, unowned or past their follow-up date. */
class EnquiriesNeedingAction extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Enquiries needing action')
            ->query(fn (): Builder => Lead::query()
                ->open()
                ->with('assignee')
                ->where(fn (Builder $query) => $query
                    ->where('status', LeadStatus::New)
                    ->orWhereNull('assigned_to')
                    ->orWhere('follow_up_at', '<', now())))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Received')->since(),
                TextColumn::make('name')->description(fn (Lead $record): ?string => $record->email ?? $record->phone),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('assignee.name')->label('Assigned to')->placeholder('Unassigned'),
                TextColumn::make('follow_up_at')
                    ->label('Follow up by')
                    ->dateTime('j M, H:i')
                    ->placeholder('—')
                    ->color(fn (Lead $record): ?string => $record->isOverdue() ? 'danger' : null),
            ])
            ->recordUrl(fn (Lead $record): string => LeadResource::getUrl('view', ['record' => $record]))
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Nothing waiting')
            ->emptyStateDescription('New, unassigned and overdue enquiries will appear here.');
    }
}
