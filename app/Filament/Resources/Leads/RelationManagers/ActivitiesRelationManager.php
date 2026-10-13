<?php

namespace App\Filament\Resources\Leads\RelationManagers;

use App\Models\Lead;
use App\Models\LeadActivity;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Attributes\On;

/** Append-only history: staff add notes; status and assignment changes log themselves. */
class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'History and notes';

    public function isReadOnly(): bool
    {
        return false;
    }

    /** Re-renders after the page's own actions log new history entries. */
    #[On('lead-updated')]
    public function refreshHistory(): void {}

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('j M Y, H:i'),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => LeadActivity::TYPES[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'note' => 'info',
                        'notification' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('body')
                    ->label('Details')
                    ->wrap(),
                TextColumn::make('user.name')
                    ->label('By')
                    ->placeholder('Website'),
            ])
            ->headerActions([
                Action::make('addNote')
                    ->label('Add note')
                    ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                    ->schema([
                        Textarea::make('body')
                            ->label('Note')
                            ->required()
                            ->rows(4)
                            ->maxLength(2000)
                            ->helperText('Visible to staff only.'),
                    ])
                    ->action(function (array $data): void {
                        $lead = $this->getOwnerRecord();

                        if ($lead instanceof Lead) {
                            $lead->recordActivity('note', (string) $data['body'], Lead::actorId());
                            Notification::make()->title('Note added')->success()->send();
                        }
                    }),
            ])
            ->paginated([10, 25, 50]);
    }
}
