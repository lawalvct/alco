<?php

namespace App\Models;

use App\Support\Navigation;
use Database\Factories\NavigationItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $location
 * @property string $label
 * @property string $url
 * @property int $sort_order
 * @property bool $is_visible
 * @property bool $opens_in_new_tab
 */
class NavigationItem extends Model
{
    /** @use HasFactory<NavigationItemFactory> */
    use HasFactory;

    public const LOCATIONS = [
        'header' => 'Header',
        'footer' => 'Footer',
    ];

    protected $fillable = ['location', 'label', 'url', 'sort_order', 'is_visible', 'opens_in_new_tab'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
            'opens_in_new_tab' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Navigation::flush());
        static::deleted(fn () => Navigation::flush());
    }

    public function isExternal(): bool
    {
        return str_starts_with($this->url, 'https://');
    }
}
