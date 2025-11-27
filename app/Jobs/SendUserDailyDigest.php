<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\NotificationStatus;
use App\Enums\TaskStatus;
use App\Models\CalendarEntry;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use App\Notifications\DailyDigestNotification;
use App\Services\QuietHoursEvaluator;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Throwable;

class SendUserDailyDigest implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly int $userId, public readonly string $targetDate)
    {
    }

    public function handle(): void
    {
        $user = User::query()->find($this->userId);

        if (! $user) {
            return;
        }

        $now = CarbonImmutable::now('UTC');

        if (QuietHoursEvaluator::isWithinQuietHours($user, $now)) {
            $next = QuietHoursEvaluator::nextAllowedMoment($user, $now);
            $this->release(max(60, $now->diffInSeconds($next)));

            return;
        }

        $tasks = $this->tasksForDate($user, $this->targetDate);
        $channels = array_values(array_unique($user->preferredNotificationChannels()));

        try {
            $user->notifyNow(new DailyDigestNotification($channels, $this->targetDate, $tasks));

            $this->logOutcome($user, $channels, NotificationStatus::Sent->value, null, count($tasks));
        } catch (Throwable $exception) {
            $this->logOutcome($user, $channels, NotificationStatus::Failed->value, $exception->getMessage(), count($tasks));

            throw $exception;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function tasksForDate(User $user, string $date): array
    {
        $startLocal = CarbonImmutable::parse($date, $user->timezone)->startOfDay();
        $endLocal = $startLocal->endOfDay();

        $startUtc = $startLocal->setTimezone('UTC');
        $endUtc = $endLocal->setTimezone('UTC');

        $tasks = Task::query()
            ->where('user_id', $user->id)
            ->status(TaskStatus::Pending->value)
            ->where(function ($builder) use ($startUtc, $endUtc, $startLocal) {
                $builder
                    ->whereBetween('due_at', [$startUtc, $endUtc])
                    ->orWhere(function ($query) use ($startLocal) {
                        $query
                            ->whereNotNull('due_date')
                            ->where('due_date', $startLocal->toDateString());
                    });
            })
            ->with('goal')
            ->get();

        $calendarTaskIds = CalendarEntry::query()
            ->where('user_id', $user->id)
            ->whereBetween('start_at', [$startUtc, $endUtc])
            ->pluck('task_id')
            ->filter()
            ->all();

        if ($calendarTaskIds !== []) {
            $calendarTasks = Task::query()
                ->where('user_id', $user->id)
                ->status(TaskStatus::Pending->value)
                ->whereIn('id', $calendarTaskIds)
                ->with('goal')
                ->get();

            $tasks = $tasks->merge($calendarTasks);
        }

        return $tasks
            ->unique('id')
            ->sortBy(function (Task $task) use ($user, $startLocal) {
                if ($task->due_at) {
                    return $task->due_at->setTimezone($user->timezone)->timestamp;
                }

                if ($task->due_date) {
                    return CarbonImmutable::parse($task->due_date->toDateString(), $user->timezone)->timestamp;
                }

                return $startLocal->timestamp;
            })
            ->map(function (Task $task) use ($user): array {
                $time = null;

                if ($task->due_at) {
                    $time = $task->due_at->setTimezone($user->timezone)->format('H:i');
                }

                return array_filter([
                    'title' => $task->title,
                    'time' => $time,
                    'goal' => $task->goal?->title,
                ], static fn ($value) => $value !== null);
            })
            ->values()
            ->all();
    }

    private function logOutcome(User $user, array $channels, string $status, ?string $error, int $count): void
    {
        foreach ($channels as $channel) {
            Notification::query()->create([
                'user_id' => $user->id,
                'channel' => $channel,
                'scheduled_for' => now(),
                'sent_at' => $status === NotificationStatus::Sent->value ? now() : null,
                'payload' => [
                    'type' => 'daily_digest',
                    'taskCount' => $count,
                    'date' => $this->targetDate,
                ],
                'status' => $status,
                'error' => $error,
            ]);
        }
    }
}
