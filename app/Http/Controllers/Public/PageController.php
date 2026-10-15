<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EngagementPackage;
use App\Models\Page;
use App\Models\Service;
use App\Models\TeamMember;
use App\Support\PageBlueprints;
use App\Support\StructuredData;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(string $slug, StructuredData $structuredData): Response
    {
        abort_unless(PageBlueprints::isCustom($slug), 404);

        $page = Page::published()
            ->where('slug', $slug)
            ->where('template_key', 'standard')
            ->firstOrFail();

        $structuredData->breadcrumbs([
            ['name' => 'Home', 'path' => '/'],
            ['name' => $page->title, 'path' => $page->publicPath()],
        ]);

        return Inertia::render('Public/ContentPage', [
            'content' => $page->publicContent(),
            'services' => Service::publicSummaries(),
            'team' => TeamMember::publicProfiles(),
            'packages' => EngagementPackage::publicCatalogue(),
        ]);
    }
}
