<?php

namespace App\Models;

use App\Models\Concerns\HasPublishedContent;
use App\Support\ArticleRichText;
use App\Support\PageBlueprints;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $slug
 * @property string $title
 * @property string $template_key
 * @property array<string, mixed> $draft_data
 * @property array<string, mixed>|null $published_data
 * @property string $publication_status
 * @property Carbon|null $published_at
 */
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, HasPublishedContent;

    protected $fillable = ['slug', 'title', 'template_key', 'draft_data'];

    protected function casts(): array
    {
        return [
            'draft_data' => 'array',
            'published_data' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * The published snapshot with library references resolved, so blocks
     * reflect the current published FAQs.
     *
     * @return array<string, mixed>|null
     */
    public function publicContent(): ?array
    {
        $content = $this->published_data;

        if (! is_array($content)) {
            return null;
        }

        $blocks = [];

        foreach (is_array($content['blocks'] ?? null) ? $content['blocks'] : [] as $block) {
            if (! is_array($block)) {
                continue;
            }

            if (($block['type'] ?? null) === 'faq' && is_array($block['data']['faq_ids'] ?? null)) {
                $block['data']['items'] = Faq::publishedItems($block['data']['faq_ids']);
                unset($block['data']['faq_ids']);
            } elseif (($block['type'] ?? null) === 'faq' && is_array($block['data']['items'] ?? null)) {
                $block['data']['items'] = array_values(array_map(
                    static fn (mixed $item): array => is_array($item) && is_string($item['answer'] ?? null)
                        ? [...$item, 'answer_html' => ArticleRichText::toSafeHtml($item['answer'])]
                        : (array) $item,
                    $block['data']['items'],
                ));
            } elseif (($block['type'] ?? null) === 'rich_text' && is_string($block['data']['body'] ?? null)) {
                $block['data']['body_html'] = ArticleRichText::toSafeHtml($block['data']['body']);
                unset($block['data']['body']);
            }

            $blocks[] = $block;
        }

        $content['blocks'] = $blocks;

        return $content;
    }

    public function publicPath(): string
    {
        return PageBlueprints::publicPath($this->slug);
    }
}
