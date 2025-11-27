<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Notifications\TaskReminderNotification;
use App\Services\QuietHoursEvaluator;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendTaskReminder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $notificationId)
    {
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(): void
    {
        $notification = Notification::query()
            ->with(['user', 'task'])
            ->find($this->notificationId);

        if (! $notification || $notification->status !== NotificationStatus::Pending->value) {
            return;
        }

        $user = $notification->user;
        $now = CarbonImmutable::now('UTC');

        if (QuietHoursEvaluator::isWithinQuietHours($user, $now)) {
            $next = QuietHoursEvaluator::nextAllowedMoment($user, $now);
            $delay = max(60, $now->diffInSeconds($next));
            $this->release($delay);

            return;
        }

        $payload = $notification->payload ?? [];

        try {
            $message = $payload['message'] ?? ($notification->task?->title ?? 'Task reminder');

            $user->notifyNow(new TaskReminderNotification($notification, $message));

            $notification->fill([
                'status' => NotificationStatus::Sent->value,
                'sent_at' => now(),
                'error' => null,
            ])->save();
        } catch (Throwable $exception) {
            $notification->fill([
                'status' => NotificationStatus::Failed->value,
                'error' => $exception->getMessage(),
            ])->save();

            throw $exception;
        }
    }
}
