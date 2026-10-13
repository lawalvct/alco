<?php

namespace App\Filament\Resources\HomepageHeroSlides\Schemas;

use App\Filament\Resources\MediaAssets\Schemas\MediaAssetForm;
use App\Models\HomepageHeroSlide;
use App\Models\MediaAsset;
use App\Support\HeroImageSpec;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

/**
 * Everything needed to put a slide live sits on this one screen. The alt
 * text and rights fields edit the chosen media-library images directly, so
 * the library stays the single source of truth.
 */
class HomepageHeroSlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Images')
                    ->description('Slides sit in the banner below the homepage heading. The heading and buttons never change between slides.')
                    ->schema([
                        self::imageSelect('media_asset_id', 'mediaAsset')
                            ->label('Main image')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::loadImageDetails($set, $get))
                            ->helperText(HeroImageSpec::desktopGuidance())
                            ->rule(fn (): Closure => self::specRule(fn (MediaAsset $asset): ?string => HeroImageSpec::desktopProblem($asset))),
                        Image::make(
                            fn (Get $get): string => self::asset($get('media_asset_id'))?->url() ?? '',
                            'Preview of the main image',
                        )
                            ->imageHeight('9rem')
                            ->visible(fn (Get $get): bool => self::asset($get('media_asset_id')) !== null),
                        self::imageSelect('mobile_media_asset_id', 'mobileMediaAsset')
                            ->label('Phone image')
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::loadImageDetails($set, $get))
                            ->helperText(HeroImageSpec::mobileGuidance())
                            ->rule(fn (): Closure => self::specRule(fn (MediaAsset $asset): ?string => HeroImageSpec::mobileProblem($asset))),
                    ]),
                Section::make('Image details')
                    ->description('Saved to the image in the media library.')
                    ->schema([
                        Toggle::make('is_decorative')
                            ->label('Decorative image')
                            ->live()
                            ->helperText('Turn on when the photo adds nothing beyond the homepage text; screen readers will skip it.'),
                        Textarea::make('image_alt_text')
                            ->label('Alt text')
                            ->rows(2)
                            ->maxLength(300)
                            ->required(fn (Get $get): bool => ! $get('is_decorative'))
                            ->hidden(fn (Get $get): bool => (bool) $get('is_decorative'))
                            ->helperText('Describe what the main image shows for people using screen readers.'),
                        Toggle::make('image_rights_confirmed')
                            ->label('ALCO has permission to use the images on this slide')
                            ->helperText('Confirms usage rights for the main and phone images. Required before the slide can be shown.'),
                    ]),
                Section::make('Framing')
                    ->description('Choose the point that must stay visible when the banner is cropped: 0 is the left or top edge, 50 the centre, 100 the right or bottom edge.')
                    ->columns(2)
                    ->schema([
                        self::focalInput('focal_x', 'Horizontal focus')->required()->default(50),
                        self::focalInput('focal_y', 'Vertical focus')->required()->default(50),
                        self::focalInput('mobile_focal_x', 'Phone horizontal focus')->placeholder('Same as main'),
                        self::focalInput('mobile_focal_y', 'Phone vertical focus')->placeholder('Same as main'),
                    ]),
                Section::make('Visibility')
                    ->schema([
                        Toggle::make('is_visible')
                            ->label('Show on homepage')
                            ->helperText(sprintf('Up to %d slides can be visible at once.', HeroImageSpec::MAX_VISIBLE_SLIDES))
                            ->rule(fn (Get $get, ?HomepageHeroSlide $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                                if (! $value) {
                                    return;
                                }

                                if (! $get('image_rights_confirmed')) {
                                    $fail('Confirm that ALCO has permission to use the images (under Image details) to show this slide.');

                                    return;
                                }

                                $otherVisible = HomepageHeroSlide::query()
                                    ->where('is_visible', true)
                                    ->when($record, fn ($query) => $query->whereKeyNot($record->getKey()))
                                    ->count();

                                if ($otherVisible >= HeroImageSpec::MAX_VISIBLE_SLIDES) {
                                    $fail(sprintf('Only %d slides can be visible. Hide another slide first.', HeroImageSpec::MAX_VISIBLE_SLIDES));
                                }
                            }),
                    ]),
            ]);
    }

    /**
     * Form values for the image fields, read from the media library.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function withImageDetails(array $data): array
    {
        $main = self::asset($data['media_asset_id'] ?? null);
        $mobile = self::asset($data['mobile_media_asset_id'] ?? null);

        return [
            ...$data,
            'image_alt_text' => $main?->alt_text,
            'image_rights_confirmed' => (bool) $main?->rights_confirmed && ($mobile === null || $mobile->rights_confirmed),
        ];
    }

    /**
     * Writes the image fields back to the media library and returns only
     * the slide's own attributes. Runs before the slide is saved, so the
     * model's rights check sees the confirmed state.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function saveImageDetails(array $data): array
    {
        $confirmed = (bool) ($data['image_rights_confirmed'] ?? false);
        $main = self::asset($data['media_asset_id'] ?? null);
        $mobile = self::asset($data['mobile_media_asset_id'] ?? null);

        if ($main) {
            $main->rights_confirmed = $confirmed;

            if (empty($data['is_decorative'])) {
                $main->alt_text = $data['image_alt_text'] ?? $main->alt_text;
            }

            $main->save();
        }

        $mobile?->forceFill(['rights_confirmed' => $confirmed])->save();

        unset($data['image_alt_text'], $data['image_rights_confirmed']);

        return $data;
    }

    private static function loadImageDetails(Set $set, Get $get): void
    {
        $details = self::withImageDetails([
            'media_asset_id' => $get('media_asset_id'),
            'mobile_media_asset_id' => $get('mobile_media_asset_id'),
        ]);

        $set('image_alt_text', $details['image_alt_text']);
        $set('image_rights_confirmed', $details['image_rights_confirmed']);
    }

    private static function asset(mixed $id): ?MediaAsset
    {
        return is_numeric($id) ? MediaAsset::find((int) $id) : null;
    }

    private static function imageSelect(string $name, string $relationship): Select
    {
        return Select::make($name)
            ->relationship($relationship, 'title')
            ->getOptionLabelFromRecordUsing(fn (MediaAsset $record): string => "{$record->title} ({$record->dimensionsLabel()})")
            ->searchable()
            ->preload()
            ->createOptionForm(MediaAssetForm::uploadFields())
            ->createOptionUsing(fn (array $data): int => (int) MediaAsset::create([
                ...$data,
                'uploaded_by' => auth()->id(),
            ])->getKey());
    }

    private static function focalInput(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->integer()
            ->minValue(0)
            ->maxValue(100)
            ->suffix('%');
    }

    /** @param  Closure(MediaAsset): ?string  $check */
    private static function specRule(Closure $check): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($check): void {
            $asset = self::asset($value);
            $problem = $asset ? $check($asset) : null;

            if ($problem !== null) {
                $fail($problem);
            }
        };
    }
}
