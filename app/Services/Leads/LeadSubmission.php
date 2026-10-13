<?php

namespace App\Services\Leads;

use App\Enums\LeadType;
use App\Http\Requests\Leads\ConsultationLeadRequest;
use App\Http\Requests\Leads\LeadRequest;
use App\Models\EngagementPackage;
use App\Models\Lead;
use App\Models\Service;
use App\Notifications\NewLeadReceived;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

/**
 * Saves a website enquiry, then tells staff. The enquiry is committed
 * before any email is attempted, so a mail problem never loses it.
 */
final class LeadSubmission
{
    private const DUPLICATE_WINDOW_MINUTES = 10;

    /**
     * @param  array<string, mixed>  $data  validated form input
     * @param  array<string, string>  $attribution  first-touch source details from the session
     */
    public function submit(LeadType $type, array $data, ?string $sourcePath, array $attribution = []): Lead
    {
        $fingerprint = $this->fingerprint($type, $data);

        // A double click or resubmission returns the enquiry already saved.
        $existing = Lead::query()
            ->where('fingerprint', $fingerprint)
            ->where('created_at', '>=', now()->subMinutes(self::DUPLICATE_WINDOW_MINUTES))
            ->first();

        if ($existing) {
            return $existing;
        }

        $service = is_numeric($data['service_id'] ?? null) ? Service::published()->find((int) $data['service_id']) : null;
        $packageSlug = is_string($data['package'] ?? null) ? $data['package'] : null;
        $package = $packageSlug && $packageSlug !== ConsultationLeadRequest::TAILORED
            ? EngagementPackage::published()->where('is_selectable', true)->where('slug', $packageSlug)->first()
            : null;

        $lead = DB::transaction(function () use ($type, $data, $service, $package, $packageSlug, $sourcePath, $attribution, $fingerprint): Lead {
            $lead = Lead::create([
                'type' => $type,
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'service_id' => $service?->id,
                'service_title' => $service?->publicSummary()['title'],
                'engagement_package_id' => $package?->id,
                'package_snapshot' => $package ? $this->packageSnapshot($package) : null,
                'wants_tailored' => $packageSlug === ConsultationLeadRequest::TAILORED,
                'message' => filled($data['message'] ?? null) ? $data['message'] : null,
                'preferred_contact_method' => $data['preferred_contact_method'] ?? null,
                'availability' => filled($data['availability'] ?? null) ? $data['availability'] : null,
                'consent_text' => LeadRequest::CONSENT_TEXT,
                'consented_at' => now(),
                'source_path' => $sourcePath,
                'attribution' => $attribution !== [] ? $attribution : null,
                'fingerprint' => $fingerprint,
            ]);

            $lead->recordActivity('submitted', 'Received through the website '.Str::lower($type->getLabel()).' form.');

            return $lead;
        });

        $this->notifyStaff($lead);

        return $lead;
    }

    private function notifyStaff(Lead $lead): void
    {
        $recipients = SiteSettings::notificationEmails();

        if ($recipients === []) {
            $lead->forceFill(['notification_status' => 'skipped'])->save();
            $lead->recordActivity('notification', 'No staff email address is set in Site settings → Enquiries, so no email was sent.');

            return;
        }

        try {
            Notification::route('mail', $recipients)->notify(new NewLeadReceived($lead));
        } catch (Throwable $exception) {
            NewLeadReceived::recordFailure($lead, $exception);
            report($exception);
        }
    }

    /** @return array{slug: string, name: string, summary: string, includes: string|null, inclusions: list<string>} */
    private function packageSnapshot(EngagementPackage $package): array
    {
        $parent = $package->parent_id
            ? EngagementPackage::published()->find($package->parent_id)
            : null;
        $summary = $package->publicSummary($parent);

        return [
            'slug' => $summary['slug'],
            'name' => $summary['name'],
            'summary' => $summary['summary'],
            'includes' => $summary['includes']['name'] ?? null,
            'inclusions' => $summary['inclusions'],
        ];
    }

    /** @param  array<string, mixed>  $data */
    private function fingerprint(LeadType $type, array $data): string
    {
        return hash('sha256', implode('|', [
            $type->value,
            Str::lower((string) ($data['email'] ?? '')),
            preg_replace('/\D/', '', (string) ($data['phone'] ?? '')),
            Str::squish(Str::lower((string) ($data['message'] ?? ''))),
            (string) ($data['package'] ?? ''),
        ]));
    }
}
