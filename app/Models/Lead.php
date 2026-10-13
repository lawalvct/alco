<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Enums\LeadType;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * An enquiry from the contact or consultation form. Private to staff.
 *
 * @property int $id
 * @property string $reference
 * @property LeadType $type
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property int|null $service_id
 * @property string|null $service_title
 * @property int|null $engagement_package_id
 * @property array{slug: string, name: string, summary: string, includes: string|null, inclusions: list<string>}|null $package_snapshot
 * @property bool $wants_tailored
 * @property string|null $message
 * @property string|null $preferred_contact_method
 * @property string|null $availability
 * @property string $consent_text
 * @property Carbon $consented_at
 * @property LeadStatus $status
 * @property int|null $assigned_to
 * @property string|null $next_action
 * @property Carbon|null $follow_up_at
 * @property string|null $source_path
 * @property array<string, string>|null $attribution
 * @property string|null $fingerprint
 * @property string $notification_status
 * @property Carbon|null $notified_at
 * @property Carbon $created_at
 * @property-read User|null $assignee
 */
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    public const CONTACT_METHODS = [
        'email' => 'Email',
        'phone' => 'Phone call',
        'whatsapp' => 'WhatsApp',
        'any' => 'No preference',
    ];

    protected $fillable = [
        'type',
        'name',
        'email',
        'phone',
        'service_id',
        'service_title',
        'engagement_package_id',
        'package_snapshot',
        'wants_tailored',
        'message',
        'preferred_contact_method',
        'availability',
        'consent_text',
        'consented_at',
        'status',
        'assigned_to',
        'next_action',
        'follow_up_at',
        'source_path',
        'attribution',
        'fingerprint',
    ];

    protected $attributes = [
        'status' => 'new',
        'notification_status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'type' => LeadType::class,
            'status' => LeadStatus::class,
            'package_snapshot' => 'array',
            'attribution' => 'array',
            'wants_tailored' => 'boolean',
            'consented_at' => 'datetime',
            'follow_up_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Lead $lead): void {
            $lead->reference ??= self::newReference();
        });

        // Workflow changes are recorded whichever screen or action made them.
        static::updated(function (Lead $lead): void {
            $actor = self::actorId();

            if ($lead->wasChanged('status')) {
                $from = LeadStatus::tryFrom((string) $lead->getRawOriginal('status'));
                $lead->recordActivity('status', sprintf(
                    'Status changed from %s to %s.',
                    $from?->getLabel() ?? 'unknown',
                    $lead->status->getLabel(),
                ), $actor);
            }

            if ($lead->wasChanged('assigned_to')) {
                $lead->recordActivity('assignment', $lead->assignee
                    ? "Assigned to {$lead->assignee->name}."
                    : 'Unassigned.', $actor);
            }

            if ($lead->wasChanged(['next_action', 'follow_up_at'])) {
                $lead->recordActivity('follow_up', $lead->follow_up_at
                    ? sprintf('Follow-up set for %s%s', $lead->follow_up_at->timezone('Africa/Lagos')->format('j M Y, H:i'), $lead->next_action ? ": {$lead->next_action}" : '.')
                    : 'Follow-up cleared.', $actor);
            }
        });
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return BelongsTo<EngagementPackage, $this> */
    public function package(): BelongsTo
    {
        return $this->belongsTo(EngagementPackage::class, 'engagement_package_id');
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return HasMany<LeadActivity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest('created_at')->latest('id');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', array_map(fn (LeadStatus $status): string => $status->value, LeadStatus::closed()));
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->open()->whereNotNull('follow_up_at')->where('follow_up_at', '<', now());
    }

    public function isOverdue(): bool
    {
        return $this->status->isOpen() && $this->follow_up_at !== null && $this->follow_up_at->isPast();
    }

    public function packageLabel(): ?string
    {
        if ($this->package_snapshot !== null) {
            return $this->package_snapshot['name'];
        }

        return $this->wants_tailored ? 'Tailored engagement' : null;
    }

    public function recordActivity(string $type, string $body, ?int $userId = null): LeadActivity
    {
        return $this->activities()->create([
            'type' => $type,
            'body' => $body,
            'user_id' => $userId,
        ]);
    }

    /** The signed-in staff member making a change, if any. */
    public static function actorId(): ?int
    {
        $user = auth()->user();

        return $user instanceof User ? $user->id : null;
    }

    /** Short code the visitor can quote, e.g. ENQ-7KX3PD. */
    public static function newReference(): string
    {
        do {
            $reference = 'ENQ-'.Str::upper(Str::random(6));
            $reference = strtr($reference, ['0' => 'X', 'O' => 'K', '1' => 'Q', 'I' => 'P', 'L' => 'M']);
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }
}
