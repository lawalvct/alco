<?php

namespace App\Filament\Resources\HomepageHeroSlides\Tables;

use App\Models\HomepageHeroSlide;
use App\Support\HeroImageSpec;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class HomepageHeroSlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['mediaAsset', 'mobileMediaAsset']))
            ->description(fn (): string => self::homepageState())
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('mediaAsset.path')
                    ->label('Image')
                    ->disk('public')
                    ->imageHeight(56),
                TextColumn::make('mediaAsset.title')
                    ->label('Main image')
                    ->description(fn (HomepageHeroSlide $record): ?string => $record->mobileMediaAsset
                        ? "Phone image: {$record->mobileMediaAsset->title}"
                        : null),
                TextColumn::make('status')
                    ->badge()
                    ->state(fn (HomepageHeroSlide $record): string => self::status($record))
                    ->color(fn (string $state): string => match ($state) {
                        'On homepage' => 'success',
                        'Hidden' => 'gray',
                        default => 'warning',
                    }),
            ])
            ->recordActions([
                self::showAction(),
                Action::make('hide')
                    ->label('Hide')
                    ->icon(Heroicon::OutlinedEyeSlash)
                    ->color('gray')
                    ->visible(fn (HomepageHeroSlide $record): bool => $record->is_visible)
                    ->action(function (HomepageHeroSlide $record): void {
                        $record->update(['is_visible' => false, 'updated_by' => auth()->id()]);

                        Notification::make()->title('Slide hidden from the homepage')->success()->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('No slides yet')
            ->emptyStateDescription('Until a slide is shown, the homepage uses the standard ALCO banner image.');
    }

    private static function showAction(): Action
    {
        return Action::make('show')
            ->label('Show on homepage')
            ->icon(Heroicon::OutlinedEye)
            ->color('success')
            ->visible(fn (HomepageHeroSlide $record): bool => ! $record->is_visible)
            ->modalHeading('Show this slide on the homepage?')
            ->modalSubmitActionLabel('Show slide')
            ->schema(fn (HomepageHeroSlide $record): array => array_values(array_filter([
                self::needsRights($record)
                    ? Checkbox::make('confirm_rights')
                        ->label('ALCO has permission to use the images on this slide')
                        ->accepted()
                        ->validationMessages(['accepted' => 'Confirm usage rights to show this slide.'])
                    : null,
                self::needsAltText($record)
                    ? Textarea::make('alt_text')
                        ->label('Alt text for the main image')
                        ->helperText('Describe what the image shows for people using screen readers.')
                        ->required()
                        ->maxLength(300)
                    : null,
            ])))
            ->action(function (HomepageHeroSlide $record, array $data): void {
                if (! empty($data['confirm_rights'])) {
                    $record->mediaAsset->forceFill(['rights_confirmed' => true])->save();
                    $record->mobileMediaAsset?->forceFill(['rights_confirmed' => true])->save();
                }

                if (filled($data['alt_text'] ?? null)) {
                    $record->mediaAsset->forceFill(['alt_text' => $data['alt_text']])->save();
                }

                try {
                    $record->update(['is_visible' => true, 'updated_by' => auth()->id()]);
                } catch (ValidationException $exception) {
                    Notification::make()
                        ->title('Slide not shown')
                        ->body(collect($exception->errors())->flatten()->first())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()->title('Slide is now on the homepage')->success()->send();
            });
    }

    private static function status(HomepageHeroSlide $record): string
    {
        return match (true) {
            $record->is_visible => 'On homepage',
            self::needsRights($record) => 'Rights not confirmed',
            self::needsAltText($record) => 'Needs alt text',
            default => 'Hidden',
        };
    }

    private static function needsRights(HomepageHeroSlide $record): bool
    {
        return ! $record->mediaAsset->rights_confirmed
            || ($record->mobileMediaAsset !== null && ! $record->mobileMediaAsset->rights_confirmed);
    }

    private static function needsAltText(HomepageHeroSlide $record): bool
    {
        return ! $record->is_decorative && blank($record->mediaAsset->alt_text);
    }

    private static function homepageState(): string
    {
        $visible = HomepageHeroSlide::query()->where('is_visible', true)->count();

        return $visible === 0
            ? 'No slide is on the homepage, so it shows the standard ALCO banner image. Use “Show on homepage” on a slide to switch it on.'
            : sprintf(
                'The homepage is showing %d of a maximum %d slides, in this order. Drag rows to reorder.',
                $visible,
                HeroImageSpec::MAX_VISIBLE_SLIDES,
            );
    }
}
