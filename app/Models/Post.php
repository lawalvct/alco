<?php

namespace App\Models;

use App\Models\Concerns\HasPublishedContent;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Public insight article. Its visitor-facing fields live in a publication
 * snapshot so saved editorial changes stay private until the next release.
 *
 * @property string $slug
 * @property string $title
 * @property string $review_status
 * @property array<string, mixed> $draft_data
 * @property array<string, mixed>|null $published_data
 * @property Carbon|null $published_at
 */
class Post extends Model
{
    use HasPublishedContent {
        publish as private publishContentSnapshot;
    }

    public const REVIEW_STATUSES = [
        'draft' => 'Draft',
        'in_review' => 'In review',
        'approved' => 'Approved for publication',
    ];

    protected $fillable = [
        'slug',
        'title',
        'review_status',
        'cover_media_asset_id',
        'draft_data',
    ];

    protected function casts(): array
    {
        return [
            'draft_data' => 'array',
            'published_data' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function coverMediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'cover_media_asset_id');
    }

    /** @return BelongsToMany<ArticleCategory, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ArticleCategory::class, 'article_category_post');
    }

    /** @return BelongsToMany<ArticleTag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ArticleTag::class, 'article_post_tag');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeInCategory(Builder $query, string $categorySlug): Builder
    {
        return $query->whereHas('categories', fn (Builder $categories) => $categories->where('slug', $categorySlug));
    }

    /** @return array{slug: string, title: string, excerpt: string, categories: list<string>, published_at: string|null, reading_minutes: int, cover: array{src: string, srcset: string|null, width: int|null, height: int|null, alt: string}|null} */
    public function publicSummary(): array
    {
        $content = $this->published_data ?? [];

        return [
            'slug' => $this->slug,
            'title' => $this->publishedTitle(),
            'excerpt' => is_string($content['excerpt'] ?? null) ? $content['excerpt'] : '',
            'categories' => $this->categories->pluck('name')->values()->all(),
            'published_at' => $this->published_at?->toDateString(),
            'reading_minutes' => $this->readingMinutes(),
            'cover' => $this->coverImage(),
        ];
    }

    /** @return array{slug: string, title: string, excerpt: string, body: string, seo_description: string, author_name: string|null, reviewer_name: string|null, reviewed_at: string|null, categories: list<string>, tags: list<string>, published_at: string|null, reading_minutes: int, cover: array{src: string, srcset: string|null, width: int|null, height: int|null, alt: string}|null, sources: list<array{title: string, url: string, accessed_on: string|null}>, related_service_ids: list<int>, related_post_ids: list<int>} */
    public function publicDetail(): array
    {
        $content = $this->published_data ?? [];

        return [
            ...$this->publicSummary(),
            'body' => is_string($content['body'] ?? null) ? $content['body'] : '',
            'seo_description' => is_string($content['seo_description'] ?? null) && $content['seo_description'] !== ''
                ? $content['seo_description']
                : (is_string($content['excerpt'] ?? null) ? $content['excerpt'] : ''),
            'author_name' => is_string($content['author_name'] ?? null) ? $content['author_name'] : null,
            'reviewer_name' => is_string($content['reviewer_name'] ?? null) ? $content['reviewer_name'] : null,
            'reviewed_at' => is_string($content['reviewed_at'] ?? null) ? $content['reviewed_at'] : null,
            'tags' => $this->tags->pluck('name')->values()->all(),
            'sources' => self::sources($content['sources'] ?? []),
            'related_service_ids' => self::ids($content['related_service_ids'] ?? []),
            'related_post_ids' => self::ids($content['related_post_ids'] ?? []),
        ];
    }

    /** @return list<array{slug: string, title: string, excerpt: string, categories: list<string>, published_at: string|null, reading_minutes: int, cover: array{src: string, srcset: string|null, width: int|null, height: int|null, alt: string}|null}> */
    public static function publishedSummaries(array $ids): array
    {
        $ids = self::ids($ids);

        if ($ids === []) {
            return [];
        }

        $posts = static::published()
            ->whereKey($ids)
            ->with(['categories', 'coverMediaAsset'])
            ->get()
            ->keyBy('id');

        $summaries = [];

        foreach ($ids as $id) {
            $post = $posts->get($id);

            if ($post instanceof self) {
                $summaries[] = $post->publicSummary();
            }
        }

        return $summaries;
    }

    public function isTaxArticle(): bool
    {
        return ArticleCategory::query()
            ->whereKey(self::ids($this->draft_data['category_ids'] ?? []))
            ->where('slug', 'tax-compliance')
            ->exists();
    }

    public function canBePublished(): bool
    {
        return $this->review_status === 'approved'
            && (! $this->isTaxArticle() || (filled($this->draft_data['reviewer_name'] ?? null) && filled($this->draft_data['reviewed_at'] ?? null)));
    }

    /**
     * Publish the saved article and its taxonomies together. The editable
     * category, tag and cover selections deliberately live in draft data so
     * they cannot change a published article before this action is used.
     */
    public function publish(?CarbonInterface $when = null, ?User $publisher = null): void
    {
        if (! $this->canBePublished()) {
            throw ValidationException::withMessages([
                'review_status' => 'Mark the article as approved before publishing. Tax & Compliance articles also need a reviewer name and review date.',
            ]);
        }

        $coverId = is_numeric($this->draft_data['cover_media_asset_id'] ?? null)
            ? (int) $this->draft_data['cover_media_asset_id']
            : null;
        $categoryIds = self::ids($this->draft_data['category_ids'] ?? []);
        $tagIds = self::ids($this->draft_data['tag_ids'] ?? []);

        DB::transaction(function () use ($when, $publisher, $coverId, $categoryIds, $tagIds): void {
            $this->forceFill(['cover_media_asset_id' => $coverId])->save();
            $this->publishContentSnapshot($when, $publisher);
            $this->categories()->sync($categoryIds);
            $this->tags()->sync($tagIds);
        });
    }

    private function publishedTitle(): string
    {
        $title = $this->published_data['title'] ?? null;

        return is_string($title) && $title !== '' ? $title : $this->title;
    }

    /** @return array{src: string, srcset: string|null, width: int|null, height: int|null, alt: string}|null */
    private function coverImage(): ?array
    {
        if (! $this->coverMediaAsset instanceof MediaAsset) {
            return null;
        }

        return [...$this->coverMediaAsset->imageSource(), 'alt' => $this->coverMediaAsset->alt_text ?: ''];
    }

    private function readingMinutes(): int
    {
        $body = $this->published_data['body'] ?? '';
        $words = is_string($body) ? str_word_count(strip_tags($body)) : 0;

        return max(1, (int) ceil($words / 220));
    }

    /** @return list<int> */
    private static function ids(mixed $values): array
    {
        return array_values(array_unique(array_map('intval', array_filter(is_array($values) ? $values : [], 'is_numeric'))));
    }

    /** @return list<array{title: string, url: string, accessed_on: string|null}> */
    private static function sources(mixed $sources): array
    {
        $valid = [];

        foreach (is_array($sources) ? $sources : [] as $source) {
            if (! is_array($source) || ! is_string($source['title'] ?? null) || ! is_string($source['url'] ?? null)) {
                continue;
            }

            $url = filter_var($source['url'], FILTER_VALIDATE_URL);

            if ($url === false || ! str_starts_with($url, 'https://')) {
                continue;
            }

            $accessedOn = $source['accessed_on'] ?? null;
            $valid[] = [
                'title' => trim($source['title']),
                'url' => $url,
                'accessed_on' => is_string($accessedOn) && $accessedOn !== '' ? $accessedOn : null,
            ];
        }

        return $valid;
    }
}
