<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EngagementPackage;
use App\Models\Page;
use App\Models\Service;
use App\Models\TeamMember;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Public/About', [
            'about' => Page::published()->where('slug', 'about')->first()?->publicContent(),
            'services' => Service::publicSummaries(),
            'team' => TeamMember::publicProfiles(),
            'packages' => EngagementPackage::publicCatalogue(),
        ]);
    }
}
