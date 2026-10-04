<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

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

                    abort_unless($record instanceof Service && $user instanceof User, 403);
                    Gate::forUser($user)->authorize('publish', $record);
                    $record->publish(publisher: $user);

                    Notification::make()->title('Service draft published')->success()->send();
                }),
            Action::make('archive')
                ->label('Archive')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(function (): bool {
                    $record = $this->getRecord();

                    return $record instanceof Service && $record->publication_status === 'published';
                })
                ->action(function (): void {
                    $record = $this->getRecord();
                    $user = auth()->user();

                    abort_unless($record instanceof Service && $user instanceof User, 403);
                    Gate::forUser($user)->authorize('publish', $record);
                    $record->forceFill(['publication_status' => 'archived'])->save();

                    Notification::make()->title('Service archived')->success()->send();
                }),
        ];
    }
}
