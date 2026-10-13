<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EngagementPackage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConsultationController extends Controller
{
    /**
     * A "Choose this package" link arrives as ?package={slug}. Only a
     * published, selectable package is preselected; anything else falls
     * back to a tailored request with a short notice.
     */
    public function __invoke(Request $request): Response
    {
        $packages = collect(EngagementPackage::publicCatalogue())
            ->where('selectable', true)
            ->values();

        $requested = $request->query('package');
        $requested = is_string($requested) ? trim($requested) : '';
        $selected = $requested !== '' ? $packages->firstWhere('slug', $requested) : null;

        return Inertia::render('Public/Consultation', [
            'packages' => $packages->all(),
            'selectedPackage' => $selected,
            'packageUnavailable' => $requested !== '' && $selected === null,
        ]);
    }
}
