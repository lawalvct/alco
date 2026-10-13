<?php

namespace App\Models;

use App\Jobs\GenerateMediaVariants;
use Database\Factories\MediaAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $title
 * @property string $disk
 * @property string $path
 * @property string|null $original_name
 * @property string|null $mime_type
 * @property int|null $size
 * @property int|null $width
 * @property int|null $height
 * @property string|null $alt_text
 * @property string|null $caption
 * @property string|null $credit
 * @property string|null $usage_rights
 * @property bool $rights_confirmed
 * @property list<array{width: int, height: int, path: string, size: int}>|null $variants
 * @property string|null $variants_status pending, ready or failed
 * @property Carbon|null $created_at
 */
class MediaAsset extends Model
{
    /** @use HasFactory<MediaAssetFactory> */
    use HasFactory;

    public const ACCEPTED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public const MAX_UPLOAD_KILOBYTES = 5120;

    protected $fillable = [
        'title',
        'disk',
        'path',
        'original_name',
        'alt_text',
        'caption',
        'credit',
        'usage_rights',
        'rights_confirmed',
        'uploaded_by',
    ];

    protected $attributes = [
        'disk' => 'public',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'rights_confirmed' => 'boolean',
            'variants' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (MediaAsset $asset): void {
            if ($asset->isDirty('path')) {
                $asset->readFileMetadata();
                $asset->variants = null;
                $asset->variants_status = 'pending';
            }
        });

        static::saved(function (MediaAsset $asset): void {
            if ($asset->wasRecentlyCreated || $asset->wasChanged('path')) {
                GenerateMediaVariants::dispatch($asset->id);
            }
        });

        static::updated(function (MediaAsset $asset): void {
            $previousPath = $asset->getOriginal('path');

            if ($asset->wasChanged('path') && is_string($previousPath) && $previousPath !== '') {
                Storage::disk($asset->disk)->delete($previousPath);
            }
        });

        static::deleted(function (MediaAsset $asset): void {
            Storage::disk($asset->disk)->delete($asset->path);
            Storage::disk($asset->disk)->deleteDirectory($asset->variantsDirectory());
        });
    }

    /** @return HasMany<HomepageHeroSlide, $this> */
    public function heroSlides(): HasMany
    {
        return $this->hasMany(HomepageHeroSlide::class);
    }

    /** @return HasMany<HomepageHeroSlide, $this> */
    public function mobileHeroSlides(): HasMany
    {
        return $this->hasMany(HomepageHeroSlide::class, 'mobile_media_asset_id');
    }

    /** @return HasMany<TeamMember, $this> */
    public function teamPortraits(): HasMany
    {
        return $this->hasMany(TeamMember::class, 'portrait_media_asset_id');
    }

    /** @return HasMany<Post, $this> */
    public function postCovers(): HasMany
    {
        return $this->hasMany(Post::class, 'cover_media_asset_id');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /** Responsive WebP sources once optimisation has finished, otherwise null. */
    public function srcset(): ?string
    {
        if ($this->variants_status !== 'ready' || empty($this->variants)) {
            return null;
        }

        $disk = Storage::disk($this->disk);

        return collect($this->variants)
            ->map(fn (array $variant): string => $disk->url($variant['path']).' '.$variant['width'].'w')
            ->implode(', ');
    }

    /** @return array{src: string, srcset: string|null, width: int|null, height: int|null} */
    public function imageSource(): array
    {
        return [
            'src' => $this->url(),
            'srcset' => $this->srcset(),
            'width' => $this->width,
            'height' => $this->height,
        ];
    }

    public function variantsDirectory(): string
    {
        return "media/variants/{$this->id}";
    }

    /** Count of records that display this asset; in-use assets cannot be deleted. */
    public function usageCount(): int
    {
        return $this->heroSlides()->count()
            + $this->mobileHeroSlides()->count()
            + $this->teamPortraits()->count()
            + $this->postCovers()->count();
    }

    public function isInUse(): bool
    {
        return $this->usageCount() > 0;
    }

    public function dimensionsLabel(): string
    {
        return $this->width && $this->height ? "{$this->width} × {$this->height} px" : 'Unknown size';
    }

    private function readFileMetadata(): void
    {
        $disk = Storage::disk($this->disk);

        if (blank($this->path) || ! $disk->exists($this->path)) {
            return;
        }

        $this->size = $disk->size($this->path);
        $this->mime_type = $disk->mimeType($this->path) ?: null;
        $this->width = null;
        $this->height = null;

        if (in_array($this->mime_type, self::ACCEPTED_MIME_TYPES, true)) {
            $dimensions = getimagesizefromstring((string) $disk->get($this->path));

            if ($dimensions !== false) {
                [$this->width, $this->height] = $dimensions;
            }
        }
    }
}
