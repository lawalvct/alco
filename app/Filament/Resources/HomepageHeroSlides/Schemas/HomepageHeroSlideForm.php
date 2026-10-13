<?php

namespace App\Filament\Resources\HomepageHeroSlides\Schemas;

use App\Filament\Resources\MediaAssets\Schemas\MediaAssetForm;
use App\Models\HomepageHeroSlide;
use App\Models\MediaAsset;
use App\Support\HeroImageSpec;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

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
                            ->helperText(HeroImageSpec::desktopGuidance())
                            ->rule(fn (): Closure => self::specRule(fn (MediaAsset $asset): ?string => HeroImageSpec::desktopProblem($asset))),
                        self::imageSelect('mobile_media_asset_id', 'mobileMediaAsset')
                            ->label('Phone image')
                            ->helperText(HeroImageSpec::mobileGuidance())
                            ->rule(fn (): Closure => self::specRule(fn (MediaAsset $asset): ?string => HeroImageSpec::mobileProblem($asset))),
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
                Section::make('Accessibility and visibility')
                    ->schema([
                        Toggle::make('is_decorative')
                            ->label('Decorative image')
                            ->helperText('Turn on when the photo adds nothing beyond the homepage text; screen readers will skip it. When off, the image’s alt text is read out.')
                            ->rule(fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                $assetId = $get('media_asset_id');
                                $asset = is_numeric($assetId) ? MediaAsset::find((int) $assetId) : null;

                                if (! $value && $asset && blank($asset->alt_text)) {
                                    $fail('Add alt text to the main image in the media library, or mark this slide as decorative.');
                                }
                            }),
                        Toggle::make('is_visible')
                            ->label('Show on homepage')
                            ->helperText(sprintf('Up to %d slides can be visible. Each image needs confirmed usage rights.', HeroImageSpec::MAX_VISIBLE_SLIDES))
                            ->rule(fn (Get $get, ?HomepageHeroSlide $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                                if (! $value) {
                                    return;
                                }

                                $otherVisible = HomepageHeroSlide::query()
                                    ->where('is_visible', true)
                                    ->when($record, fn ($query) => $query->whereKeyNot($record->getKey()))
                                    ->count();

                                if ($otherVisible >= HeroImageSpec::MAX_VISIBLE_SLIDES) {
                                    $fail(sprintf('Only %d slides can be visible. Hide another slide first.', HeroImageSpec::MAX_VISIBLE_SLIDES));

                                    return;
                                }

                                $unconfirmed = MediaAsset::query()
                                    ->whereKey(array_filter([$get('media_asset_id'), $get('mobile_media_asset_id')]))
                                    ->where('rights_confirmed', false)
                                    ->exists();

                                if ($unconfirmed) {
                                    $fail('Confirm usage rights for this slide’s images in the media library first.');
                                }
                            }),
                    ]),
            ]);
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
            $asset = is_numeric($value) ? MediaAsset::find((int) $value) : null;
            $problem = $asset ? $check($asset) : null;

            if ($problem !== null) {
                $fail($problem);
            }
        };
    }
}
