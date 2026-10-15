<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Concerns\HasPublicationActions;
use App\Filament\Resources\Pages\PageResource;
use App\Support\PageBlueprints;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;

class EditPage extends EditRecord
{
    use HasPublicationActions;

    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        $publicationActions = $this->publicationActions('Page');
        $publicationActions[0]->color('gray');
        $publicationActions[1]->visible(fn (): bool => $this->getRecord()->publication_status === 'published'
            && (PageBlueprints::isCustom($this->getRecord()->slug) || $this->getRecord()->template_key === 'legal'));

        return [
            Action::make('saveAndPublish')
                ->label('Save & publish page')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Save the changes currently in this form and publish them immediately for visitors.')
                ->action(function (): void {
                    Gate::authorize('publish', $this->getRecord());
                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                    $record = $this->getRecord()->refresh();
                    $record->publish(publisher: auth()->user());

                    Notification::make()
                        ->title('Page saved and published')
                        ->body('Visitors can now see the changes.')
                        ->success()
                        ->send();
                }),
            ...$publicationActions,
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Draft saved')
            ->body('Visitors still see the last published version. Use “Save & publish page” when the changes are ready.')
            ->success();
    }
}
