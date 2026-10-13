<?php

namespace App\Models;

use App\Support\HeroImageSpec;
use Database\Factories\HomepageHeroSlideFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property int $media_asset_id
 * @property int|null $mobile_media_asset_id
 * @property int $focal_x
 * @property int $focal_y
 * @property int|null $mobile_focal_x
 * @property int|null $mobile_focal_y
 * @property bool $is_decorative
 * @property int $sort_order
 * @property bool $is_visible
 * @property-read MediaAsset $mediaAsset
 * @property-read MediaAsset|null $mobileMediaAsset
 */
class HomepageHeroSlide extends Model
{
    /** @use HasFactory<HomepageHeroSlideFactory> */
    use HasFactory;

    protected $fillable = [
        'media_asset_id',
        'mobile_media_asset_id',
        'focal_x',
        'focal_y',
        'mobile_focal_x',
        'mobile_focal_y',
        'is_decorative',
        'sort_order',
        'is_visible',
        'created_by',
        'updated_by',
    ];

    protected $attributes = [
        'focal_x' => 50,
        'focal_y' => 50,
    ];

    protected function casts(): array
    {
        return [
            'focal_x' => 'integer',
            'focal_y' => 'integer',
            'mobile_focal_x' => 'integer',
            'mobile_focal_y' => 'integer',
            'is_decorative' => 'boolean',
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    /**
     * Server-side backstop for the admin form rules, so no code path can
     * show more than ten slides or an image without confirmed rights.
     */
    protected static function booted(): void
    {
        static::saving(function (HomepageHeroSlide $slide): void {
            if (! $slide->is_visible || ! $slide->isDirty(['is_visible', 'media_asset_id', 'mobile_media_asset_id'])) {
                return;
            }

            $otherVisible = static::query()
                ->where('is_visible', true)
                ->when($slide->exists, fn (Builder $query) => $query->whereKeyNot($slide->getKey()))
                ->count();

            if ($otherVisible >= HeroImageSpec::MAX_VISIBLE_SLIDES) {
                throw ValidationException::withMessages([
                    'is_visible' => sprintf('Only %d slides can be visible. Hide another slide first.', HeroImageSpec::MAX_VISIBLE_SLIDES),
                ]);
            }

            $assets = MediaAsset::query()
                ->whereKey(array_filter([$slide->media_asset_id, $slide->mobile_media_asset_id]))
                ->get();

            if ($assets->contains(fn (MediaAsset $asset): bool => ! $asset->rights_confirmed)) {
                throw ValidationException::withMessages([
                    'is_visible' => 'Confirm usage rights for this slide’s images in the media library before showing it.',
                ]);
            }
        });
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function mobileMediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'mobile_media_asset_id');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return array{id: int, src: string, width: int|null, height: int|null, alt: string, position: string, mobile: array{src: string, width: int|null, height: int|null}|null, mobile_position: string}
     */
    public function publicPayload(): array
    {
        $mobile = $this->mobileMediaAsset;

        return [
            'id' => $this->id,
            'src' => $this->mediaAsset->url(),
            'width' => $this->mediaAsset->width,
            'height' => $this->mediaAsset->height,
            'alt' => $this->is_decorative ? '' : (string) $this->mediaAsset->alt_text,
            'position' => "{$this->focal_x}% {$this->focal_y}%",
            'mobile' => $mobile ? [
                'src' => $mobile->url(),
                'width' => $mobile->width,
                'height' => $mobile->height,
            ] : null,
            'mobile_position' => sprintf(
                '%d%% %d%%',
                $this->mobile_focal_x ?? $this->focal_x,
                $this->mobile_focal_y ?? $this->focal_y,
            ),
        ];
    }
}
