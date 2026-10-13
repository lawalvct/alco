<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per typed settings group. Read through App\Support\SiteSettings.
 *
 * @property string $group
 * @property array<string, mixed> $data
 */
class SiteSetting extends Model
{
    protected $fillable = ['group', 'data', 'updated_by'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
