<?php

namespace App\Models;

use Database\Factories\TeamMemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string|null $role
 * @property string|null $bio
 * @property int|null $portrait_media_asset_id
 * @property int $sort_order
 * @property bool $is_published
 * @property-read MediaAsset|null $portrait
 */
class TeamMember extends Model
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory;

    protected $fillable = ['name', 'role', 'bio', 'portrait_media_asset_id', 'sort_order', 'is_published'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function portrait(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'portrait_media_asset_id');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('sort_order')->orderBy('id');
    }

    /** @return array{id: int, name: string, role: string|null, bio: string|null, portrait: array{src: string, srcset: string|null, width: int|null, height: int|null}|null} */
    public function publicProfile(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role,
            'bio' => $this->bio,
            'portrait' => $this->portrait?->imageSource(),
        ];
    }
}
