<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use App\Models\Calculator;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\TopicHub;
use App\Support\PageBlueprints;
use App\Support\StructuredData;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFactory;

/**
 * Public, indexable pages only. Drafts, previews, forms and placeholder
 * pages are left out so search engines never see them listed.
 */
class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $pages = Page::published()->get()->keyBy('slug');
        $urls = [];

        $add = function (string $path, CarbonInterface|string|null $modified = null) use (&$urls): void {
            $urls[] = ['loc' => StructuredData::absolute($path), 'lastmod' => $modified === null ? null : Carbon::parse($modified)->toDateString()];
        };

        $add('/', $pages->get('home')?->published_at);
        $add('/about', $pages->get('about')?->published_at);
        $add('/services');
        $add('/faq');
        $add('/contact');
        $add('/insights');
        $add('/tools');

        foreach (Calculator::query()->where('is_visible', true)->orderBy('sort_order')->get() as $calculator) {
            $add("/tools/{$calculator->slug}", $calculator->updated_at);
        }

        foreach (Service::published()->orderBy('sort_order')->get() as $service) {
            $add("/services/{$service->slug}", $service->published_at);
        }

        foreach (Post::published()->orderByDesc('published_at')->get() as $post) {
            $add("/insights/{$post->slug}", $post->published_at);
        }

        foreach (ArticleCategory::visible()->whereHas('posts', fn ($query) => $query->published())->get() as $category) {
            $add("/insights/category/{$category->slug}");
        }

        foreach (TopicHub::published()->get() as $topic) {
            $add("/insights/topic/{$topic->slug}", $topic->published_at);
        }

        foreach (['privacy', 'terms', 'calculator-disclaimer'] as $slug) {
            $legal = $pages->get($slug);

            if ($legal && $legal->template_key === 'legal') {
                $add("/{$slug}", $legal->published_at);
            }
        }

        foreach ($pages as $page) {
            if ($page->template_key === 'standard' && PageBlueprints::isCustom($page->slug)) {
                $add($page->publicPath(), $page->published_at);
            }
        }

        return ResponseFactory::make(
            view('sitemap', ['urls' => $urls])->render(),
            200,
            ['Content-Type' => 'application/xml; charset=UTF-8'],
        );
    }

    /** Production allows crawling; any other environment asks crawlers to stay away. */
    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? [
                'User-agent: *',
                'Disallow: /admin',
                'Disallow: /login',
                'Disallow: /dashboard',
                'Disallow: /settings',
                '',
                'Sitemap: '.StructuredData::absolute('/sitemap.xml'),
            ]
            : ['User-agent: *', 'Disallow: /'];

        return ResponseFactory::make(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
