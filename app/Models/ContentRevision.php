<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentRevision extends Model
{
    public $timestamps = false;

    protected $fillable = ['payload', 'published_by', 'published_at'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function contentable(): MorphTo
    {
        return $this->morphTo();
    }
}
