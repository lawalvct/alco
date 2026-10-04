<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publishDraft')
                ->label('Publish saved draft')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (): void {
                    $record = $this->getRecord();
                    $user = auth()->user();

                    abort_unless($record instanceof Page && $user instanceof User, 403);
                    Gate::forUser($user)->authorize('publish', $record);
                    $record->publish(publisher: $user);

                    Notification::make()->title('Homepage draft published')->success()->send();
                }),
            Action::make('archive')
                ->label('Archive')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(function (): bool {
                    $record = $this->getRecord();

                    return $record instanceof Page && $record->publication_status === 'published';
                })
                ->action(function (): void {
                    $record = $this->getRecord();
                    $user = auth()->user();

                    abort_unless($record instanceof Page && $user instanceof User, 403);
                    Gate::forUser($user)->authorize('publish', $record);
                    $record->forceFill(['publication_status' => 'archived'])->save();

                    Notification::make()->title('Page archived')->success()->send();
                }),
        ];
    }
}
