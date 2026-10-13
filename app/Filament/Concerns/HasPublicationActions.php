<?php

namespace App\Filament\Concerns;

use App\Models\EngagementPackage;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\TopicHub;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Publish/archive header actions for edit pages of draft-and-publish
 * content. Publishing releases the last saved draft as a new revision.
 */
trait HasPublicationActions
{
    /** @return list<Action> */
    protected function publicationActions(string $label): array
    {
        return [
            Action::make('publishDraft')
                ->label('Publish saved draft')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription(function (): string {
                    $record = $this->publishableRecord();

                    if ($record instanceof Post && ($blocker = $record->publicationBlocker()) !== null) {
                        return $blocker.' Save any changes on this screen first.';
                    }

                    return 'Visitors will see the last saved version. Save any changes on this screen first.';
                })
                ->modalSubmitActionLabel("Publish {$label}")
                ->action(function () use ($label): void {
                    $record = $this->publishableRecord();
                    $user = $this->publishingUser();

                    Gate::forUser($user)->authorize('publish', $record);

                    if ($record instanceof Post && ($blocker = $record->publicationBlocker()) !== null) {
                        Notification::make()
                            ->title("{$label} is not ready to publish")
                            ->body($blocker)
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }

                    try {
                        $record->publish(publisher: $user);
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title("{$label} could not be published")
                            ->body(collect($exception->errors())->flatten()->implode(' '))
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }

                    Notification::make()->title("{$label} draft published")->success()->send();
                }),
            Action::make('archive')
                ->label('Archive')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('Visitors will no longer see this content. Publishing again restores it.')
                ->visible(fn (): bool => $this->publishableRecord()->publication_status === 'published')
                ->action(function () use ($label): void {
                    $record = $this->publishableRecord();

                    Gate::forUser($this->publishingUser())->authorize('publish', $record);
                    $record->forceFill(['publication_status' => 'archived'])->save();

                    Notification::make()->title("{$label} archived")->success()->send();
                }),
        ];
    }

    private function publishableRecord(): Page|Service|EngagementPackage|Post|TopicHub
    {
        $record = $this->getRecord();

        abort_unless($record instanceof Page || $record instanceof Service || $record instanceof EngagementPackage || $record instanceof Post || $record instanceof TopicHub, 403);

        return $record;
    }

    private function publishingUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }
}
