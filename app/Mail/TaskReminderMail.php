<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly ?Task $task,
        public readonly string $body
    ) {
    }

    public function build(): self
    {
        return $this->subject('Task Reminder')
            ->view('emails.task-reminder');
    }
}
