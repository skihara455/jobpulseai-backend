<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApplicationSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public Application $application) {}

    public function via(object $notifiable): array
    {
        return ['database']; // stored in notifications table
    }

    public function toArray(object $notifiable): array
    {
        $job = $this->application->job;
        $user = $this->application->user;

        return [
            'type'           => 'application.submitted',
            'message'        => "{$user->name} applied to your job: {$job->title}",
            'application_id' => $this->application->id,
            'job_id'         => $job->id,
            'job_title'      => $job->title,
            'applicant_id'   => $user->id,
            'applicant_name' => $user->name,
        ];
    }
}
