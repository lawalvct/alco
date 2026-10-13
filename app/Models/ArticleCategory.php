<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ArticleCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'seo_description', 'is_visible', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsToMany<Post, $this> */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'article_category_post');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('sort_order')->orderBy('name');
    }

    /** @return list<array{slug: string, name: string}> */
    public static function publicOptions(): array
    {
        return static::visible()
            ->whereHas('posts', fn (Builder $query) => $query->published())
            ->get(['id', 'slug', 'name'])
            ->map(fn (self $category): array => ['slug' => $category->slug, 'name' => $category->name])
            ->all();
    }
}
