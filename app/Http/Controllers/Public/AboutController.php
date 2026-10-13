<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Public/Section', [
            'kind' => 'about',
            'team' => TeamMember::published()
                ->with('portrait')
                ->get()
                ->map(fn (TeamMember $member): array => $member->publicProfile())
                ->all(),
        ]);
    }
}
