<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\TaskReminderMail;
use App\Models\Notification as NotificationModel;
use App\Notifications\Channels\WebPushChannel;
use Illuminate\Notifications\Notification;

class TaskReminderNotification extends Notification
{
    public function __construct(private readonly NotificationModel $record, private readonly string $message)
    {
    }

    public function via($notifiable): array
    {
        $channel = $this->record->channel instanceof \BackedEnum
            ? $this->record->channel->value
            : $this->record->channel;

        return $channel === 'email'
            ? ['mail']
            : [WebPushChannel::class];
    }

    public function toMail($notifiable): TaskReminderMail
    {
        return new TaskReminderMail($notifiable, $this->record->task, $this->message);
    }

    public function toWebPush($notifiable): array
    {
        return [
            'title' => 'Task Reminder',
            'body' => $this->message,
            'data' => [
                'taskId' => $this->record->task_id,
            ],
        ];
    }
}
