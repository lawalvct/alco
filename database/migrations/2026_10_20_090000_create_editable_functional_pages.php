<?php

use App\Support\PageBlueprints;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['home' => PageBlueprints::HOME_FIXED_COPY, 'about' => PageBlueprints::ABOUT_FIXED_COPY] as $slug => $defaults) {
            $page = DB::table('pages')->where('slug', $slug)->first(['id', 'draft_data', 'published_data']);

            if ($page === null) {
                continue;
            }

            $draft = json_decode((string) $page->draft_data, true) ?: [];
            $published = $page->published_data === null ? null : (json_decode((string) $page->published_data, true) ?: []);

            DB::table('pages')->where('id', $page->id)->update([
                'draft_data' => json_encode(array_replace($defaults, $draft), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                'published_data' => $published === null ? null : json_encode(array_replace($defaults, $published), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]);
        }

        foreach (PageBlueprints::DEFAULT_COPY as $slug => $content) {
            if (DB::table('pages')->where('slug', $slug)->exists()) {
                continue;
            }

            $snapshot = json_encode($content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

            DB::table('pages')->insert([
                'slug' => $slug,
                'title' => $content['title'],
                'template_key' => 'standard',
                'draft_data' => $snapshot,
                'published_data' => $snapshot,
                'publication_status' => 'published',
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Keep editors' content and revisions intact if this migration is rolled back.
    }
};
