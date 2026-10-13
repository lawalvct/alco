<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The owner asked (13 Oct 2026) for alcosolutionsng@gmail.com to be used
 * alongside info@alcosolutions.ng, listed first, for the public contact
 * details and for new-enquiry emails. Saved settings move from a single
 * `email` to an ordered `emails` list with the Gmail address first.
 */
return new class extends Migration
{
    private const PRIMARY = 'alcosolutionsng@gmail.com';

    public function up(): void
    {
        $this->update('contact', function (array $data): array {
            $existing = $data['emails'] ?? (isset($data['email']) ? [$data['email']] : []);
            unset($data['email']);
            $data['emails'] = $this->withPrimaryFirst($existing);

            return $data;
        });

        $this->update('forms', function (array $data): array {
            $data['notification_emails'] = $this->withPrimaryFirst($data['notification_emails'] ?? []);

            return $data;
        });

        Cache::forget('site-settings.stored');
    }

    public function down(): void
    {
        // Keeps the saved addresses; nothing to undo safely.
    }

    /** @param  callable(array<string, mixed>): array<string, mixed>  $change */
    private function update(string $group, callable $change): void
    {
        $row = DB::table('site_settings')->where('group', $group)->first();

        if (! $row) {
            return;
        }

        $data = json_decode((string) $row->data, true);

        DB::table('site_settings')->where('group', $group)->update([
            'data' => json_encode($change(is_array($data) ? $data : [])),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  mixed  $emails
     * @return list<string>
     */
    private function withPrimaryFirst($emails): array
    {
        $others = array_filter(
            is_array($emails) ? $emails : [],
            fn ($email): bool => is_string($email) && strtolower(trim($email)) !== self::PRIMARY,
        );

        return array_values([self::PRIMARY, ...$others]);
    }
};
