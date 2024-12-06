<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $tasks;

    public function __construct(User $user, $tasks)
    {
        $this->user = $user;
        $this->tasks = $tasks;
    }

    public function build()
    {
        return $this->subject('Your Daily Task Notifications')
                    ->markdown('emails.task_notification');
    }
}
