<?php

namespace App\Models;

use App\Models\Concerns\HasPublishedContent;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $slug
 * @property string $title
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
}
