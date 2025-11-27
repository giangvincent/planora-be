<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class TaskReminderService
{
    /**
     * Get tasks that need reminders
     */
    public function getTasksDueForReminder(): array
    {
        $now = Carbon::now();
        $oneHourFromNow = $now->copy()->addHour();

        // Get tasks due within the next hour that haven't been completed
        $tasks = Task::with('user')
            ->where('status', 'pending')
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$now, $oneHourFromNow])
            ->get();

        return $tasks->toArray();
    }

    /**
     * Send reminder for a specific task
     */
    public function sendReminder(User $user, Task $task): void
    {
        $channels = $user->preferredNotificationChannels();

        foreach ($channels as $channel) {
            if ($channel === 'email') {
                // Send email (implement later with Mailable)
                // Mail::to($user)->send(new TaskReminderMail($task));
            }

            if ($channel === 'push') {
                $pushService = app(WebPushService::class);
                $pushService->sendTaskReminder($user, $task);
            }
        }
    }
}
