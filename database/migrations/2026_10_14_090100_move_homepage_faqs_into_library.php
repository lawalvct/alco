<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Homepage FAQ blocks used to store their questions inline. They now pick
 * from the FAQ library, so existing inline questions become library entries
 * and the draft block references them. Published snapshots are left alone;
 * the site keeps showing them until the homepage is published again.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('pages')->get(['id', 'draft_data', 'published_at']) as $page) {
            $data = json_decode((string) $page->draft_data, true);

            if (! is_array($data) || ! is_array($data['blocks'] ?? null)) {
                continue;
            }

            $changed = false;

            foreach ($data['blocks'] as $index => $block) {
                $items = $block['data']['items'] ?? null;

                if (($block['type'] ?? null) !== 'faq' || ! is_array($items)) {
                    continue;
                }

                $ids = [];

                foreach ($items as $item) {
                    $question = trim((string) ($item['question'] ?? ''));
                    $answer = trim((string) ($item['answer'] ?? ''));

                    if ($question === '' || $answer === '') {
                        continue;
                    }

                    $id = DB::table('faqs')->where('question', $question)->value('id')
                        ?? DB::table('faqs')->insertGetId([
                            'question' => $question,
                            'answer' => $answer,
                            'group' => 'General',
                            'sort_order' => (int) DB::table('faqs')->max('sort_order') + 1,
                            // Already visible on a published homepage, so keep them public.
                            'is_published' => $page->published_at !== null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                    $ids[] = (string) $id;
                }

                unset($data['blocks'][$index]['data']['items']);
                $data['blocks'][$index]['data']['faq_ids'] = $ids;
                $changed = true;
            }

            if ($changed) {
                DB::table('pages')->where('id', $page->id)->update(['draft_data' => json_encode($data)]);
            }
        }
    }

    public function down(): void
    {
        // Library entries are kept; nothing to undo safely.
    }
};
