<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EngagementPackage;
use App\Models\Faq;
use App\Models\Service;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Section', [
            'kind' => 'services',
            'publishedServices' => Service::published()
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Service $service): array => $service->publicSummary())
                ->all(),
            'packages' => EngagementPackage::publicCatalogue(),
        ]);
    }

    public function show(string $slug): Response
    {
        $service = Service::published()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Public/Service', [
            'service' => $service->publicDetail(),
            'packages' => EngagementPackage::publicCatalogue($service->id),
            'faqs' => $service->faqs()
                ->published()
                ->get()
                ->map(fn (Faq $faq): array => $faq->publicItem())
                ->all(),
        ]);
    }
}
