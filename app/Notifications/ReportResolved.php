<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportResolved extends Notification
{
    use Queueable;

    public function __construct(public Report $report) {}

    public function via(object $notifiable): array
    {
        return $notifiable->notifications_enabled ? ['database'] : [];
    }

    public function toArray(object $notifiable): array
    {
        $label = $this->report->status === Report::STATUS_RESOLVED ? 'traité' : 'rejeté';

        return [
            'message' => 'Votre signalement a été '.$label.' par la modération.',
            'report_id' => $this->report->id,
        ];
    }
}
