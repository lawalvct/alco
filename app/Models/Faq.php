<?php

namespace App\Models;

use App\Support\ArticleRichText;
use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property string $group
 * @property int $sort_order
 * @property bool $is_published
 */
class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory;

    protected $fillable = ['question', 'answer', 'group', 'sort_order', 'is_published'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /** @return BelongsToMany<Service, $this> */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('sort_order')->orderBy('id');
    }

    /** @return array{id: int, question: string, answer_html: string} */
    public function publicItem(): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'answer_html' => ArticleRichText::toSafeHtml($this->answer),
        ];
    }

    /**
     * Published FAQs in the given order; unknown or unpublished ids are skipped.
     *
     * @param  array<mixed>  $ids
     * @return list<array{id: int, question: string, answer_html: string}>
     */
    public static function publishedItems(array $ids): array
    {
        $ids = array_values(array_map('intval', array_filter($ids, 'is_numeric')));
        $faqs = static::published()->whereKey($ids)->get()->keyBy('id');

        $items = [];

        foreach ($ids as $id) {
            $faq = $faqs->get($id);

            if ($faq instanceof self) {
                $items[] = $faq->publicItem();
            }
        }

        return $items;
    }
}
