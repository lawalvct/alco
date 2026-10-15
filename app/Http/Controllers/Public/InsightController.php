<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use App\Models\Post;
use App\Models\Service;
use App\Models\TopicHub;
use App\Support\PageBlueprints;
use App\Support\StructuredData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InsightController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->listing($request);
    }

    public function category(Request $request, string $category): Response
    {
        $selectedCategory = ArticleCategory::visible()
            ->where('slug', $category)
            ->whereHas('posts', fn (Builder $query) => $query->published())
            ->firstOrFail();

        return $this->listing($request, $selectedCategory);
    }

    public function show(string $slug, StructuredData $structuredData): Response
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->with(['categories', 'tags', 'coverMediaAsset'])
            ->firstOrFail();
        $detail = $post->publicDetail();
        $this->describeArticle($structuredData, $detail);
        $relatedPosts = Post::publishedSummaries($detail['related_post_ids']);

        if ($relatedPosts === []) {
            $relatedPosts = Post::published()
                ->whereKeyNot($post->getKey())
                ->whereHas('categories', fn (Builder $query) => $query->whereIn('article_categories.id', $post->categories->modelKeys()))
                ->with(['categories', 'coverMediaAsset'])
                ->orderByDesc('published_at')
                ->limit(3)
                ->get()
                ->map(fn (Post $related): array => $related->publicSummary())
                ->all();
        }

        $relatedServices = $this->relatedServices($detail['related_service_ids']);

        return Inertia::render('Public/Article', [
            'post' => $detail,
            'relatedPosts' => $relatedPosts,
            'relatedServices' => $relatedServices,
        ]);
    }

    /** @param  array<string, mixed>  $detail */
    private function describeArticle(StructuredData $structuredData, array $detail): void
    {
        $path = "/insights/{$detail['slug']}";
        $modified = $detail['reviewed_at'] ?? $detail['published_at'];

        $structuredData->add(array_filter([
            '@type' => 'BlogPosting',
            'headline' => $detail['title'],
            'description' => $detail['seo_description'] ?: null,
            'url' => StructuredData::absolute($path),
            'mainEntityOfPage' => StructuredData::absolute($path),
            'datePublished' => $detail['published_at'],
            'dateModified' => $modified,
            'author' => $detail['author_name']
                ? ['@type' => 'Person', 'name' => $detail['author_name']]
                : ['@id' => StructuredData::absolute('/#organization')],
            'publisher' => ['@id' => StructuredData::absolute('/#organization')],
            'image' => isset($detail['cover']['src']) ? StructuredData::absolute($detail['cover']['src']) : null,
        ], fn (mixed $value): bool => $value !== null && $value !== ''));

        $structuredData->breadcrumbs([
            ['name' => 'Home', 'path' => '/'],
            ['name' => 'Insights', 'path' => '/insights'],
            ['name' => (string) $detail['title'], 'path' => $path],
        ]);
    }

    public function topic(string $slug): Response
    {
        $hub = TopicHub::published()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Public/TopicHub', [
            'topic' => $hub->publicContent(),
        ]);
    }

    private function listing(Request $request, ?ArticleCategory $selectedCategory = null): Response
    {
        $term = trim((string) $request->query('q', ''));
        $term = mb_substr($term, 0, 100);

        $articles = Post::published()
            ->with(['categories', 'coverMediaAsset'])
            ->when($selectedCategory, fn (Builder $query) => $query->inCategory($selectedCategory->slug))
            ->when($term !== '', function (Builder $query) use ($term): void {
                $query->where(function (Builder $query) use ($term): void {
                    $query
                        ->where('title', 'like', "%{$term}%")
                        ->orWhere('published_data->excerpt', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (Post $post): array => $post->publicSummary());

        return Inertia::render('Public/InsightsIndex', [
            'pageCopy' => PageBlueprints::copyFor('insights'),
            'articles' => $articles,
            'categories' => ArticleCategory::publicOptions(),
            'selectedCategory' => $selectedCategory ? [
                'slug' => $selectedCategory->slug,
                'name' => $selectedCategory->name,
                'description' => $selectedCategory->description,
                'seo_description' => $selectedCategory->seo_description,
            ] : null,
            'filters' => ['q' => $term],
        ]);
    }

    /** @param list<int> $ids @return list<array{slug: string, title: string, summary: string}> */
    private function relatedServices(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $services = Service::published()->whereKey($ids)->get()->keyBy('id');
        $items = [];

        foreach ($ids as $id) {
            $service = $services->get($id);

            if ($service instanceof Service) {
                $items[] = $service->publicSummary();
            }
        }

        return $items;
    }
}
