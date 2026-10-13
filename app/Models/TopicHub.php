<?php

namespace App\Models;

use App\Models\Concerns\HasPublishedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/** @property array<string, mixed>|null $published_data @property Carbon|null $published_at */
class TopicHub extends Model
{
    use HasPublishedContent;

    protected $fillable = ['slug', 'title', 'sort_order', 'draft_data'];

    protected function casts(): array
    {
        return [
            'draft_data' => 'array',
            'published_data' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /** @return array{slug: string, title: string, intro: string, seo_description: string, featured: array{slug: string, title: string, excerpt: string, categories: list<string>, published_at: string|null, reading_minutes: int, cover: array{src: string, srcset: string|null, width: int|null, height: int|null, alt: string}|null}|null, articles: list<array{slug: string, title: string, excerpt: string, categories: list<string>, published_at: string|null, reading_minutes: int, cover: array{src: string, srcset: string|null, width: int|null, height: int|null, alt: string}|null}>} */
    public function publicContent(): array
    {
        $content = $this->published_data ?? [];
        $articleIds = is_array($content['post_ids'] ?? null) ? $content['post_ids'] : [];
        $articles = Post::publishedSummaries($articleIds);
        $featuredId = is_numeric($content['featured_post_id'] ?? null) ? (int) $content['featured_post_id'] : null;
        $featuredSlug = $featuredId === null
            ? null
            : Post::published()->whereKey($featuredId)->value('slug');
        $featured = null;

        foreach ($articles as $article) {
            if ($featuredSlug !== null && $article['slug'] === $featuredSlug) {
                $featured = $article;

                break;
            }
        }

        if ($featured === null && $articles !== []) {
            $featured = array_shift($articles);
        } else {
            $articles = array_values(array_filter($articles, fn (array $article): bool => $article !== $featured));
        }

        return [
            'slug' => $this->slug,
            'title' => $this->publishedTitle(),
            'intro' => is_string($content['intro'] ?? null) ? $content['intro'] : '',
            'seo_description' => is_string($content['seo_description'] ?? null) ? $content['seo_description'] : '',
            'featured' => $featured,
            'articles' => $articles,
        ];
    }

    private function publishedTitle(): string
    {
        $title = $this->published_data['title'] ?? null;

        return is_string($title) && $title !== '' ? $title : $this->title;
    }
}
