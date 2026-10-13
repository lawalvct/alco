<?php

namespace App\Filament\Tables;

use App\Models\EngagementPackage;
use App\Models\Page;
use App\Models\Service;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

/** Status columns shared by draft-and-publish content tables. */
final class PublicationColumns
{
    /** @return list<TextColumn|IconColumn> */
    public static function make(): array
    {
        return [
            TextColumn::make('publication_status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (string $state): string => ucfirst($state))
                ->color(fn (string $state): string => match ($state) {
                    'published' => 'success',
                    'archived' => 'warning',
                    default => 'gray',
                }),
            IconColumn::make('unpublished_changes')
                ->label('Draft changes')
                ->state(fn (Page|Service|EngagementPackage $record): bool => $record->hasUnpublishedChanges())
                ->boolean()
                ->trueIcon(Heroicon::OutlinedPencilSquare)
                ->trueColor('warning')
                ->falseIcon(Heroicon::OutlinedCheckCircle)
                ->falseColor('gray')
                ->tooltip(fn (bool $state): string => $state
                    ? 'The saved draft differs from what visitors see.'
                    : 'Visitors see the saved draft.'),
            TextColumn::make('published_at')
                ->label('Published')
                ->dateTime('j M Y, H:i')
                ->sortable()
                ->placeholder('Never'),
        ];
    }
}
