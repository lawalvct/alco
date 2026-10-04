<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
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
        ]);
    }

    public function show(string $slug): Response
    {
        $service = Service::published()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Public/Service', [
            'service' => $service->publicDetail(),
        ]);
    }
}
