<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Support\PageBlueprints;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function __invoke(): Response
    {
        $groups = Faq::published()
            ->get()
            ->groupBy('group')
            ->map(fn ($faqs, string $group): array => [
                'name' => $group,
                'items' => $faqs->map(fn (Faq $faq): array => $faq->publicItem())->values()->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('Public/Faq', [
            'pageCopy' => PageBlueprints::copyFor('faq'),
            'groups' => $groups,
        ]);
    }
}
