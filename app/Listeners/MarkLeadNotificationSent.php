<?php

namespace App\Listeners;

use App\Notifications\NewLeadReceived;
use Illuminate\Notifications\Events\NotificationSent;

class MarkLeadNotificationSent
{
    public function handle(NotificationSent $event): void
    {
        if (! $event->notification instanceof NewLeadReceived) {
            return;
        }

        $lead = $event->notification->lead;

        if ($lead->notification_status !== 'sent') {
            $lead->forceFill(['notification_status' => 'sent', 'notified_at' => now()])->save();
        }
    }
}
