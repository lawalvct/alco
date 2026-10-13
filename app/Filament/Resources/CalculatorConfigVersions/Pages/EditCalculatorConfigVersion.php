<?php

namespace App\Filament\Resources\CalculatorConfigVersions\Pages;

use App\Filament\Resources\CalculatorConfigVersions\CalculatorConfigVersionResource;
use App\Models\CalculatorConfigVersion;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class EditCalculatorConfigVersion extends EditRecord
{
    protected static string $resource = CalculatorConfigVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [Action::make('approve')
            ->label('Approve saved rule')
            ->color('success')
            ->requiresConfirmation()
            ->modalDescription('Confirm the reviewed source, scope, rate and effective dates. Approved versions cannot be edited; create a new version for later changes.')
            ->visible(fn (): bool => $this->rule()->status !== 'approved')
            ->action(function (): void {
                $rule = $this->rule();
                Gate::authorize('approve', $rule);

                try {
                    $rule->approve(auth()->user());
                    Notification::make()->title('Tax calculator rule approved')->success()->send();
                } catch (ValidationException $exception) {
                    Notification::make()->title('Rule could not be approved')->body(collect($exception->errors())->flatten()->implode(' '))->danger()->persistent()->send();
                }
            })];
    }

    private function rule(): CalculatorConfigVersion
    {
        $record = $this->getRecord();
        abort_unless($record instanceof CalculatorConfigVersion, 403);

        return $record;
    }
}
