<?php

namespace App\Models;

use App\Models\Concerns\HasPublishedContent;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $slug
 * @property string $title
 * @property int $sort_order
 * @property array<string, mixed> $draft_data
 * @property array<string, mixed>|null $published_data
 * @property string $publication_status
 * @property Carbon|null $published_at
 */
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, HasPublishedContent;

    protected $fillable = ['slug', 'title', 'sort_order', 'draft_data'];

    protected function casts(): array
    {
        return [
            'draft_data' => 'array',
            'published_data' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /** @return array{slug: string, title: string, summary: string} */
    public function publicSummary(): array
    {
        $content = $this->published_data ?? [];

        return [
            'slug' => $this->slug,
            'title' => is_string($content['title'] ?? null) ? $content['title'] : $this->title,
            'summary' => is_string($content['summary'] ?? null) ? $content['summary'] : '',
        ];
    }

    /** @return array{slug: string, title: string, summary: string, body: string, audience: string, seo_description: string, deliverables: list<array{label: string}>} */
    public function publicDetail(): array
    {
        $content = $this->published_data ?? [];
        $deliverables = [];

        foreach ($content['deliverables'] ?? [] as $item) {
            if (is_array($item) && is_string($item['label'] ?? null) && $item['label'] !== '') {
                $deliverables[] = ['label' => $item['label']];
            }
        }

        return [
            ...$this->publicSummary(),
            'body' => is_string($content['body'] ?? null) ? $content['body'] : '',
            'audience' => is_string($content['audience'] ?? null) ? $content['audience'] : '',
            'seo_description' => is_string($content['seo_description'] ?? null) && $content['seo_description'] !== ''
                ? $content['seo_description']
                : (is_string($content['summary'] ?? null) ? $content['summary'] : ''),
            'deliverables' => $deliverables,
        ];
    }
}
