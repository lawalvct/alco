<?php

namespace App\Http\Controllers\Public\Concerns;

use App\Enums\LeadType;
use App\Http\Middleware\CaptureAttribution;
use App\Http\Requests\Leads\LeadRequest;
use App\Models\Service;
use App\Services\Leads\LeadSubmission;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

trait HandlesLeadSubmissions
{
    private function submitLead(LeadRequest $request, LeadType $type, LeadSubmission $submission, string $returnRoute): RedirectResponse
    {
        if (! SiteSettings::formsEnabled()) {
            return redirect()->route($returnRoute)->withErrors([
                'form' => 'Online enquiries are paused at the moment. Please call or email us instead.',
            ]);
        }

        // Bots that fill the hidden field get the normal success screen, but nothing is saved or sent.
        if ($request->isLikelySpam()) {
            Inertia::flash('leadSubmitted', ['reference' => null]);

            return redirect()->route($returnRoute);
        }

        $attribution = $request->session()->get(CaptureAttribution::SESSION_KEY, []);

        $lead = $submission->submit(
            $type,
            $request->validated(),
            parse_url((string) $request->headers->get('referer'), PHP_URL_PATH) ?: null,
            is_array($attribution) ? $attribution : [],
        );

        Inertia::flash('leadSubmitted', ['reference' => $lead->reference]);

        return redirect()->route($returnRoute);
    }

    /** @return list<array{id: int, title: string}> */
    private function serviceOptions(): array
    {
        return array_values(Service::published()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Service $service): array => [
                'id' => (int) $service->getKey(),
                'title' => $service->publicSummary()['title'],
            ])
            ->all());
    }
}
