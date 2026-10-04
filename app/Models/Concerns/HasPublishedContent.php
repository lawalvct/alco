<?php

namespace App\Models\Concerns;

use App\Models\ContentRevision;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

trait HasPublishedContent
{
    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('publication_status', 'published')
            ->whereNotNull('published_data')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** @return MorphMany<ContentRevision, $this> */
    public function revisions(): MorphMany
    {
        return $this->morphMany(ContentRevision::class, 'contentable');
    }

    public function publish(?CarbonInterface $when = null, ?User $publisher = null): void
    {
        if (blank($this->title)) {
            throw ValidationException::withMessages([
                'title' => 'Add a title before publishing.',
            ]);
        }

        $when ??= now();
        $snapshot = array_merge($this->draft_data ?? [], ['title' => $this->title]);

        DB::transaction(function () use ($when, $publisher, $snapshot): void {
            $this->forceFill([
                'published_data' => $snapshot,
                'publication_status' => 'published',
                'published_at' => $when,
                'published_by' => $publisher?->id,
            ])->save();

            $this->revisions()->create([
                'payload' => $snapshot,
                'published_by' => $publisher?->id,
                'published_at' => $when,
            ]);
        });
    }
}
