<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Models\Lead;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Follow-up')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('status')->badge(),
                        TextEntry::make('assignee.name')->label('Assigned to')->placeholder('Unassigned'),
                        TextEntry::make('follow_up_at')
                            ->label('Follow up by')
                            ->dateTime('j M Y, H:i')
                            ->placeholder('Not set')
                            ->color(fn (Lead $record): ?string => $record->isOverdue() ? 'danger' : null),
                        TextEntry::make('next_action')->label('Next action')->placeholder('—'),
                    ]),
                Section::make('Contact')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')
                            ->placeholder('Not given')
                            ->url(fn (Lead $record): ?string => $record->email ? "mailto:{$record->email}" : null)
                            ->copyable(),
                        TextEntry::make('phone')
                            ->placeholder('Not given')
                            ->url(fn (Lead $record): ?string => $record->phone ? 'tel:'.preg_replace('/[^\d+]/', '', $record->phone) : null)
                            ->copyable(),
                        TextEntry::make('preferred_contact_method')
                            ->label('Prefers')
                            ->formatStateUsing(fn (?string $state): string => Lead::CONTACT_METHODS[$state] ?? '—')
                            ->placeholder('—'),
                        TextEntry::make('availability')->placeholder('—')->columnSpanFull(),
                    ]),
                Section::make('Request')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('type')->badge(),
                        TextEntry::make('created_at')->label('Received')->dateTime('j M Y, H:i'),
                        TextEntry::make('service_title')->label('Service')->placeholder('Not specified'),
                        TextEntry::make('package')
                            ->label('Package')
                            ->state(fn (Lead $record): ?string => $record->packageLabel())
                            ->helperText(fn (Lead $record): ?string => $record->package_snapshot
                                ? 'As shown when submitted: '.implode(', ', array_filter([
                                    $record->package_snapshot['includes'] ? "everything in {$record->package_snapshot['includes']}" : null,
                                    ...$record->package_snapshot['inclusions'],
                                ]))
                                : null)
                            ->placeholder('None chosen'),
                        TextEntry::make('message')
                            ->placeholder('No message')
                            ->columnSpanFull()
                            ->prose()
                            ->formatStateUsing(fn (?string $state): string => nl2br(e((string) $state)))
                            ->html(),
                    ]),
                Section::make('Record')
                    ->collapsed()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('reference'),
                        TextEntry::make('notification_status')
                            ->label('Staff email')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'sent' => 'Sent',
                                'failed' => 'Failed',
                                'skipped' => 'Not sent (no address set)',
                                default => 'Waiting to send',
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'sent' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('consent_text')->label('Consent given')->helperText(fn (Lead $record): string => 'Agreed '.$record->consented_at->timezone('Africa/Lagos')->format('j M Y, H:i')),
                        TextEntry::make('source')
                            ->label('Source')
                            ->state(fn (Lead $record): string => self::sourceSummary($record)),
                    ]),
            ]);
    }

    private static function sourceSummary(Lead $record): string
    {
        $parts = [];
        $attribution = $record->attribution ?? [];

        if ($record->source_path) {
            $parts[] = "Form on {$record->source_path}";
        }

        if (isset($attribution['utm_source'])) {
            $parts[] = 'Campaign: '.implode(' / ', array_filter([
                $attribution['utm_source'],
                $attribution['utm_medium'] ?? null,
                $attribution['utm_campaign'] ?? null,
            ]));
        }

        if (isset($attribution['referrer'])) {
            $parts[] = "Came from {$attribution['referrer']}";
        }

        if (isset($attribution['landing_page'])) {
            $parts[] = "First page {$attribution['landing_page']}";
        }

        return $parts === [] ? 'Not recorded' : implode(' · ', $parts);
    }
}
