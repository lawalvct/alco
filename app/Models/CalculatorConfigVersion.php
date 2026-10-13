<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @property string $tool_slug
 * @property string $scope_key
 * @property string $status
 * @property string $label
 * @property numeric-string $rate_percent
 * @property string|null $reviewer_name
 * @property string|null $source_url
 * @property Carbon $effective_from
 * @property Carbon|null $effective_to
 * @property Carbon|null $reviewed_at
 */
class CalculatorConfigVersion extends Model
{
    protected $fillable = [
        'tool_slug', 'scope_key', 'version', 'label', 'rate_percent',
        'effective_from', 'effective_to', 'reviewer_name', 'reviewed_at',
        'source_url', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'reviewed_at' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (self $rule): void {
            if ($rule->getOriginal('status') === 'approved') {
                throw ValidationException::withMessages(['status' => 'Approved rules are immutable. Create a new version instead.']);
            }
        });
    }

    public function approve(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $rule = self::query()->lockForUpdate()->whereKey($this->getKey())->firstOrFail();

            if ($rule->status === 'approved') {
                return;
            }

            if (blank($rule->reviewer_name) || $rule->reviewed_at === null || blank($rule->source_url)) {
                throw ValidationException::withMessages(['reviewer_name' => 'Add the reviewer, review date and source URL before approval.']);
            }

            if (! in_array($rule->tool_slug, ['vat', 'withholding-tax'], true)
                || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $rule->scope_key)
                || ! preg_match('#^https://#', $rule->source_url)
                || bccomp((string) $rule->rate_percent, '0', 5) < 0
                || bccomp((string) $rule->rate_percent, '100', 5) > 0) {
                throw ValidationException::withMessages(['rate_percent' => 'Choose a valid tax tool, rule key, HTTPS source and rate between 0 and 100%.']);
            }

            if ($rule->effective_to && $rule->effective_to->lt($rule->effective_from)) {
                throw ValidationException::withMessages(['effective_to' => 'The end date must be on or after the start date.']);
            }

            $overlap = self::query()
                ->where('tool_slug', $rule->tool_slug)
                ->where('scope_key', $rule->scope_key)
                ->where('status', 'approved')
                ->where('effective_from', '<=', $rule->effective_to?->toDateString() ?? '9999-12-31')
                ->where(fn ($query) => $query->whereNull('effective_to')->orWhere('effective_to', '>=', $rule->effective_from->toDateString()))
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages(['effective_from' => 'An approved rule already covers this tool, scope and date range.']);
            }

            $rule->forceFill(['status' => 'approved', 'approved_by' => $user->id, 'approved_at' => now()])->save();
            $this->refresh();
        });
    }

    public static function applicable(string $slug, string $scope, string $date): ?self
    {
        return self::query()
            ->where('tool_slug', $slug)
            ->where('scope_key', $scope)
            ->where('status', 'approved')
            ->whereDate('effective_from', '<=', $date)
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date))
            ->orderByDesc('version')
            ->first();
    }
}
