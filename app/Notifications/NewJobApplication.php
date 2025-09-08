<?php

namespace App\Notifications;

use App\Models\Job;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewJobApplication extends Notification
{
    use Queueable;

    public function __construct(
        public Job $job,
        public User $applicant,
        public ?string $coverLetter = null
    ) {}

    public function via(object $notifiable): array
    {
        // database channel -> stored in notifications table
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'new_job_application',
            'job_id'        => $this->job->id,
            'job_title'     => $this->job->title,
            'applicant_id'  => $this->applicant->id,
            'applicant_name'=> $this->applicant->name,
            'cover_letter'  => $this->coverLetter,
        ];
    }

    // Optional: if you later add mail, you can implement toMail()
}
