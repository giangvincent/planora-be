<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class DailyDigestService
{
    /**
     * Generate daily digest for a user
     */
    public function generateDigest(User $user): array
    {
        $today = Carbon::now($user->timezone);

        // Get tasks due today
        $tasksToday = $user->tasks()
            ->where('status', 'pending')
            ->whereDate('due_date', $today->toDateString())
            ->with('goal')
            ->get();

        // Get completed tasks from yesterday
        $yesterday = $today->copy()->subDay();
        $completedYesterday = $user->tasks()
            ->where('status', 'done')
            ->whereDate('updated_at', $yesterday->toDateString())
            ->count();

        // Get current streak
        $profile = $user->gamificationProfile;
        $streak = $profile?->current_streak ?? 0;

        // Get upcoming goals
        $upcomingGoals = $user->goals()
            ->where('status', 'active')
            ->whereNotNull('target_date')
            ->where('target_date', '>=', $today->toDateString())
            ->orderBy('target_date')
            ->limit(3)
            ->get();

        return [
            'user' => $user,
            'tasks_today' => $tasksToday,
            'completed_yesterday' => $completedYesterday,
            'current_streak' => $streak,
            'upcoming_goals' => $upcomingGoals,
            'date' => $today,
        ];
    }

    /**
     * Send digest to user via enabled channels
     */
    public function sendDigest(User $user, array $digest): void
    {
        $channels = $user->preferredNotificationChannels();

        foreach ($channels as $channel) {
            if ($channel === 'email') {
                // Send email (implement later with Mailable)
                // Mail::to($user)->send(new DailyDigestMail($digest));
            }

            if ($channel === 'push') {
                $pushService = app(WebPushService::class);
                $pushService->sendDailyDigest($user, $digest);
            }
        }
    }
}
