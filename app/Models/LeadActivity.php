<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Private, append-only history of what happened to an enquiry.
 *
 * @property int $id
 * @property int $lead_id
 * @property int|null $user_id
 * @property string $type
 * @property string $body
 * @property Carbon $created_at
 * @property-read User|null $user
 */
class LeadActivity extends Model
{
    public const UPDATED_AT = null;

    public const TYPES = [
        'submitted' => 'Received',
        'note' => 'Note',
        'status' => 'Status',
        'assignment' => 'Assignment',
        'follow_up' => 'Follow-up',
        'notification' => 'Notification',
    ];

    protected $fillable = ['type', 'body', 'user_id'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
