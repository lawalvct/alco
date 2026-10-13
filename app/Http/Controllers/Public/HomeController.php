<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EngagementPackage;
use App\Models\HomepageHeroSlide;
use App\Models\Page;
use App\Models\Service;
use App\Support\HeroImageSpec;
use App\Support\SiteSettings;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $home = Page::published()->where('slug', 'home')->first();

        return Inertia::render('Welcome', [
            'home' => $home?->publicContent(),
            'publishedServices' => Service::published()
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Service $service): array => $service->publicSummary())
                ->all(),
            'heroSlides' => HomepageHeroSlide::visible()
                ->with(['mediaAsset', 'mobileMediaAsset'])
                ->limit(HeroImageSpec::MAX_VISIBLE_SLIDES)
                ->get()
                ->map(fn (HomepageHeroSlide $slide): array => $slide->publicPayload())
                ->all(),
            'heroSlider' => SiteSettings::heroSlider(),
            'packages' => EngagementPackage::publicCatalogue(),
        ]);
    }
}
