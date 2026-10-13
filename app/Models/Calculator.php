<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property list<array{step: string}>|null $guide_steps
 * @property list<array{question: string, answer: string}>|null $guide_faqs
 */
class Calculator extends Model
{
    protected $fillable = [
        'title', 'summary', 'intro', 'currency', 'is_visible', 'sort_order',
        'seo_title', 'seo_description', 'guide_intro', 'guide_example', 'guide_steps', 'guide_faqs',
    ];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'guide_steps' => 'array', 'guide_faqs' => 'array'];
    }
}
