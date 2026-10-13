<?php

namespace App\Models;

use App\Models\Concerns\HasPublishedContent;
use Database\Factories\EngagementPackageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A customer-facing starting point for an ongoing engagement. Choosing one
 * only preselects it on the consultation request; ALCO confirms scope and
 * terms directly. Prices are intentionally not modelled yet.
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property int|null $parent_id
 * @property int $sort_order
 * @property bool $is_selectable
 * @property array<string, mixed> $draft_data
 * @property array<string, mixed>|null $published_data
 * @property string $publication_status
 * @property Carbon|null $published_at
 */
class EngagementPackage extends Model
{
    /** @use HasFactory<EngagementPackageFactory> */
    use HasFactory, HasPublishedContent;

    protected $fillable = ['slug', 'title', 'parent_id', 'sort_order', 'is_selectable', 'draft_data'];

    protected function casts(): array
    {
        return [
            'draft_data' => 'array',
            'published_data' => 'array',
            'published_at' => 'datetime',
            'is_selectable' => 'boolean',
        ];
    }

    /** @return BelongsTo<EngagementPackage, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Published packages in display order, with inherited packages resolved
     * only against other published packages so drafts never leak.
     *
     * Pass a service id to list only packages that suit that service.
     *
     * @return list<array{slug: string, name: string, summary: string, audience: string, inclusions: list<string>, includes: array{slug: string, name: string}|null, selectable: bool}>
     */
    public static function publicCatalogue(?int $forServiceId = null): array
    {
        $packages = static::published()->orderBy('sort_order')->orderBy('id')->get();
        $byId = $packages->keyBy('id');

        return array_values($packages
            ->when($forServiceId !== null, fn ($packages) => $packages->filter(
                fn (EngagementPackage $package): bool => in_array($forServiceId, $package->publishedServiceIds(), true),
            ))
            ->map(fn (EngagementPackage $package): array => $package->publicSummary(
                $package->parent_id ? $byId->get($package->parent_id) : null,
            ))
            ->all());
    }

    /**
     * @return array{slug: string, name: string, summary: string, audience: string, inclusions: list<string>, includes: array{slug: string, name: string}|null, selectable: bool}
     */
    public function publicSummary(?EngagementPackage $publishedParent = null): array
    {
        $content = $this->published_data ?? [];

        return [
            'slug' => $this->slug,
            'name' => $this->publishedName(),
            'summary' => is_string($content['summary'] ?? null) ? $content['summary'] : '',
            'audience' => is_string($content['audience'] ?? null) ? $content['audience'] : '',
            'inclusions' => self::labels($content['inclusions'] ?? []),
            'includes' => $publishedParent ? [
                'slug' => $publishedParent->slug,
                'name' => $publishedParent->publishedName(),
            ] : null,
            'selectable' => $this->is_selectable,
        ];
    }

    /** @return list<int> */
    public function publishedServiceIds(): array
    {
        $ids = $this->published_data['service_ids'] ?? [];

        return array_values(array_map('intval', array_filter(is_array($ids) ? $ids : [], 'is_numeric')));
    }

    public function publishedName(): string
    {
        $name = $this->published_data['title'] ?? null;

        return is_string($name) && $name !== '' ? $name : $this->title;
    }

    /** @return list<string> */
    private static function labels(mixed $items): array
    {
        $labels = [];

        foreach (is_array($items) ? $items : [] as $item) {
            if (is_array($item) && is_string($item['label'] ?? null) && $item['label'] !== '') {
                $labels[] = $item['label'];
            }
        }

        return $labels;
    }
}
