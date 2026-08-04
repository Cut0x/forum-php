<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContentReported extends Notification
{
    use Queueable;

    public function __construct(public Report $report) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $reportable = $this->report->reportable;
        $topic = $reportable instanceof \App\Models\Topic ? $reportable : $reportable->topic;

        return [
            'message' => $this->report->reporter->displayName().' a signalé un contenu ('.$this->report->reason.').',
            'report_id' => $this->report->id,
            'topic_id' => $topic->id,
            'topic_slug' => $topic->slug,
        ];
    }
}
