<?php

namespace App\Notifications;

use App\Enums\LeadType;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

/** Emails staff about a new website enquiry. */
class NewLeadReceived extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300];

    public function __construct(public Lead $lead) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead;
        $isConsultation = $lead->type === LeadType::Consultation;

        $mail = (new MailMessage)
            ->subject(($isConsultation ? 'New consultation request' : 'New website enquiry')." from {$lead->name} ({$lead->reference})")
            ->greeting($isConsultation ? 'New consultation request' : 'New website enquiry')
            ->line("**Reference:** {$lead->reference}")
            ->line("**Name:** {$lead->name}");

        foreach ([
            'Email' => $lead->email,
            'Phone' => $lead->phone,
            'Preferred contact' => $lead->preferred_contact_method ? Lead::CONTACT_METHODS[$lead->preferred_contact_method] ?? null : null,
            'Availability' => $lead->availability,
            'Service' => $lead->service_title,
            'Package' => $lead->packageLabel(),
        ] as $label => $value) {
            if (filled($value)) {
                $mail->line("**{$label}:** {$value}");
            }
        }

        if (filled($lead->message)) {
            $mail->line('**Message:**')->line(Str::limit((string) $lead->message, 1500));
        }

        if (filled($lead->email)) {
            $mail->replyTo((string) $lead->email, $lead->name);
        }

        return $mail
            ->action('Open the enquiry', LeadResource::getUrl('view', ['record' => $lead]))
            ->line('The enquiry is saved in the ALCO admin under Enquiries.');
    }

    public function failed(Throwable $exception): void
    {
        self::recordFailure($this->lead, $exception);
    }

    /** Marks the enquiry so staff can see the email did not go out. Safe to call twice. */
    public static function recordFailure(Lead $lead, Throwable $exception): void
    {
        $lead->refresh();

        if ($lead->notification_status === 'failed') {
            return;
        }

        $lead->forceFill(['notification_status' => 'failed'])->save();
        $lead->recordActivity('notification', 'The staff email could not be sent ('.class_basename($exception).'). The enquiry is saved; please check the mail settings.');
    }
}
