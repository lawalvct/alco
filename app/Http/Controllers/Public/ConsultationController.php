<?php

namespace App\Http\Controllers\Public;

use App\Enums\LeadType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\HandlesLeadSubmissions;
use App\Http\Requests\Leads\ConsultationLeadRequest;
use App\Http\Requests\Leads\LeadRequest;
use App\Models\EngagementPackage;
use App\Models\Lead;
use App\Services\Leads\LeadSubmission;
use App\Support\SiteSettings;
use App\Support\Turnstile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConsultationController extends Controller
{
    use HandlesLeadSubmissions;

    /**
     * A "Choose this package" link arrives as ?package={slug}. Only a
     * published, selectable package is preselected; anything else falls
     * back to a tailored request with a short notice.
     */
    public function show(Request $request): Response
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
            'services' => $this->serviceOptions(),
            'contactMethods' => Lead::CONTACT_METHODS,
            'formsEnabled' => SiteSettings::formsEnabled(),
            'turnstileSiteKey' => Turnstile::siteKey(),
            'consentText' => LeadRequest::CONSENT_TEXT,
        ]);
    }

    public function store(ConsultationLeadRequest $request, LeadSubmission $submission): RedirectResponse
    {
        return $this->submitLead($request, LeadType::Consultation, $submission, 'consultation');
    }
}
