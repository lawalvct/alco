<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\ArticleRichText;
use Inertia\Inertia;
use Inertia\Response;

/** Privacy notice, terms and calculator disclaimer. Hidden until a page is published. */
class LegalController extends Controller
{
    public function __invoke(string $slug): Response
    {
        $page = Page::published()
            ->where('template_key', 'legal')
            ->where('slug', $slug)
            ->firstOrFail();

        $content = $page->published_data ?? [];
        $sections = [];

        foreach (is_array($content['sections'] ?? null) ? $content['sections'] : [] as $section) {
            if (is_array($section) && is_string($section['heading'] ?? null) && is_string($section['body'] ?? null)) {
                $sections[] = [
                    'heading' => $section['heading'],
                    'body_html' => ArticleRichText::toSafeHtml($section['body']),
                ];
            }
        }

        return Inertia::render('Public/Legal', [
            'legal' => [
                'title' => $page->title,
                'eyebrow' => is_string($content['hero_eyebrow'] ?? null) ? $content['hero_eyebrow'] : 'Legal',
                'intro' => is_string($content['hero_description'] ?? null) ? $content['hero_description'] : null,
                'effective_date' => is_string($content['effective_date'] ?? null) ? $content['effective_date'] : null,
                'seo_description' => is_string($content['seo_description'] ?? null) ? $content['seo_description'] : null,
                'sections' => $sections,
            ],
        ]);
    }
}
