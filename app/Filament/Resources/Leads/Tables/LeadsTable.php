<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Models\Lead;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('assignee'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('j M, H:i')
                    ->description(fn (Lead $record): string => $record->created_at->diffForHumans())
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(['name', 'email', 'phone', 'reference'])
                    ->description(fn (Lead $record): ?string => $record->email ?? $record->phone),
                TextColumn::make('type')->badge(),
                TextColumn::make('interest')
                    ->state(fn (Lead $record): ?string => $record->packageLabel() ?? $record->service_title)
                    ->placeholder('—'),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('assignee.name')
                    ->label('Assigned to')
                    ->placeholder('Unassigned'),
                TextColumn::make('follow_up_at')
                    ->label('Follow up by')
                    ->dateTime('j M, H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->color(fn (Lead $record): ?string => $record->isOverdue() ? 'danger' : null)
                    ->description(fn (Lead $record): ?string => $record->next_action),
                IconColumn::make('notification_status')
                    ->label('Email')
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        'sent' => Heroicon::OutlinedCheckCircle,
                        'failed' => Heroicon::OutlinedExclamationTriangle,
                        'skipped' => Heroicon::OutlinedNoSymbol,
                        default => Heroicon::OutlinedClock,
                    })
                    ->color(fn (string $state): string => $state === 'failed' ? 'danger' : 'gray')
                    ->tooltip(fn (string $state): string => match ($state) {
                        'sent' => 'Staff email sent',
                        'failed' => 'Staff email failed; check the mail settings',
                        'skipped' => 'No staff email address set',
                        default => 'Staff email waiting to send',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options(LeadStatus::class)->multiple(),
                SelectFilter::make('type')->options(LeadType::class),
                SelectFilter::make('assigned_to')
                    ->label('Assigned to')
                    ->relationship('assignee', 'name'),
                Filter::make('unassigned')
                    ->label('Unassigned only')
                    ->query(fn (Builder $query) => $query->whereNull('assigned_to')),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markClosed')
                        ->label('Mark as spam / closed')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            foreach ($records as $lead) {
                                if ($lead instanceof Lead) {
                                    $lead->update(['status' => LeadStatus::Closed]);
                                }
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No enquiries here')
            ->emptyStateDescription('Enquiries from the contact and consultation forms appear here.');
    }
}
