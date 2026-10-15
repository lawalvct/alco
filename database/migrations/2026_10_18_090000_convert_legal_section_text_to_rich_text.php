<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Legal section bodies were plain text (a blank line started a paragraph). The
 * editor now stores HTML, so existing plain text is converted once.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('pages')->where('template_key', 'legal')->orderBy('id')->each(function (object $page): void {
            $update = [];

            foreach (['draft_data', 'published_data'] as $column) {
                $data = is_string($page->{$column}) ? json_decode($page->{$column}, true) : null;

                if (! is_array($data) || ! is_array($data['sections'] ?? null)) {
                    continue;
                }

                foreach ($data['sections'] as $index => $section) {
                    if (is_array($section) && is_string($section['body'] ?? null)) {
                        $data['sections'][$index]['body'] = $this->toHtml($section['body']);
                    }
                }

                $update[$column] = json_encode($data);
            }

            if ($update !== []) {
                DB::table('pages')->where('id', $page->id)->update($update);
            }
        });
    }

    public function down(): void
    {
        // Rich text cannot be safely turned back into plain text.
    }

    private function toHtml(string $text): string
    {
        if (preg_match('/<\/?[a-z][^>]*>/i', $text) === 1) {
            return $text;
        }

        $paragraphs = array_filter(
            preg_split('/\R{2,}/', trim($text)) ?: [],
            static fn (string $paragraph): bool => trim($paragraph) !== '',
        );

        return implode('', array_map(
            static fn (string $paragraph): string => '<p>'.nl2br(e(trim($paragraph)), false).'</p>',
            $paragraphs,
        ));
    }
};
