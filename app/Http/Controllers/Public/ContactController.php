<?php

namespace App\Http\Controllers\Public;

use App\Enums\LeadType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\HandlesLeadSubmissions;
use App\Http\Requests\Leads\ContactLeadRequest;
use App\Http\Requests\Leads\LeadRequest;
use App\Services\Leads\LeadSubmission;
use App\Support\PageBlueprints;
use App\Support\SiteSettings;
use App\Support\Turnstile;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    use HandlesLeadSubmissions;

    public function show(): Response
    {
        return Inertia::render('Public/Contact', [
            'pageCopy' => PageBlueprints::copyFor('contact'),
            'services' => $this->serviceOptions(),
            'formsEnabled' => SiteSettings::formsEnabled(),
            'turnstileSiteKey' => Turnstile::siteKey(),
            'consentText' => LeadRequest::CONSENT_TEXT,
        ]);
    }

    public function store(ContactLeadRequest $request, LeadSubmission $submission): RedirectResponse
    {
        return $this->submitLead($request, LeadType::Contact, $submission, 'contact');
    }
}
