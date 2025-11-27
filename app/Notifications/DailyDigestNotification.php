<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\DailyDigestMail;
use App\Notifications\Channels\WebPushChannel;
use Illuminate\Notifications\Notification;

class DailyDigestNotification extends Notification
{
    /**
     * @param array<int, string> $channels
     * @param array<int, array<string, mixed>> $tasks
     */
    public function __construct(
        private readonly array $channels,
        private readonly string $date,
        private readonly array $tasks
    ) {
    }

    public function via($notifiable): array
    {
        return array_map(function (string $channel) {
            return $channel === 'email' ? 'mail' : WebPushChannel::class;
        }, $this->channels);
    }

    public function toMail($notifiable): DailyDigestMail
    {
        return new DailyDigestMail($notifiable, $this->date, $this->tasks);
    }

    public function toWebPush($notifiable): array
    {
        $count = count($this->tasks);

        return [
            'title' => 'Daily Digest',
            'body' => $count === 0
                ? 'You have a clear schedule today.'
                : sprintf('You have %d task(s) planned for today.', $count),
            'data' => [
                'date' => $this->date,
            ],
        ];
    }
}
