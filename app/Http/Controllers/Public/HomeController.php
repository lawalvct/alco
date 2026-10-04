<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Service;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $home = Page::published()->where('slug', 'home')->first();

        return Inertia::render('Welcome', [
            'home' => $home?->published_data,
            'publishedServices' => Service::published()
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Service $service): array => $service->publicSummary())
                ->all(),
        ]);
    }
}
