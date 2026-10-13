<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    public function getTitle(): string
    {
        $lead = $this->getRecord();

        return $lead instanceof Lead ? "{$lead->name} · {$lead->reference}" : 'Enquiry';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assignToMe')
                ->label('Assign to me')
                ->icon(Heroicon::OutlinedUserPlus)
                ->color('gray')
                ->visible(fn (Lead $record): bool => $record->assigned_to !== auth()->id())
                ->action(function (Lead $record): void {
                    $record->update(['assigned_to' => auth()->id()]);
                    $this->dispatch('lead-updated');

                    Notification::make()->title('Assigned to you')->success()->send();
                }),
            Action::make('updateFollowUp')
                ->label('Update status')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->fillForm(fn (Lead $record): array => [
                    'status' => $record->status,
                    'assigned_to' => $record->assigned_to,
                    'next_action' => $record->next_action,
                    'follow_up_at' => $record->follow_up_at,
                ])
                ->schema([
                    Select::make('status')
                        ->options(LeadStatus::class)
                        ->required(),
                    Select::make('assigned_to')
                        ->label('Assigned to')
                        ->options(fn (): array => User::query()->where('is_admin', true)->orderBy('name')->pluck('name', 'id')->all())
                        ->placeholder('Unassigned'),
                    TextInput::make('next_action')
                        ->label('Next action')
                        ->placeholder('For example: call to agree a consultation time')
                        ->maxLength(200),
                    DateTimePicker::make('follow_up_at')
                        ->label('Follow up by')
                        ->seconds(false),
                ])
                ->action(function (Lead $record, array $data): void {
                    $record->update($data);
                    $this->dispatch('lead-updated');

                    Notification::make()->title('Enquiry updated')->success()->send();
                }),
            DeleteAction::make()
                ->modalDescription('This permanently removes the enquiry and its history. Use it for spam or when someone asks for their details to be deleted.'),
        ];
    }
}
