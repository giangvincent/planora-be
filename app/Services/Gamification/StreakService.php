<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use App\Models\GamificationProfile;
use App\Models\User;
use Carbon\Carbon;

class StreakService
{
    /**
     * Check and update user's streak
     */
    public function checkAndUpdateStreak(User $user, Carbon $today): void
    {
        $profile = $user->gamificationProfile;

        if (!$profile) {
            return;
        }

        $lastActiveDate = $profile->last_active_date;

        // No previous activity
        if (!$lastActiveDate) {
            $profile->current_streak = 1;
            $profile->longest_streak = 1;
            $profile->last_active_date = $today->toDateString();
            $profile->save();
            return;
        }

        $daysSinceLastActive = $today->diffInDays($lastActiveDate);

        // Same day - no change
        if ($daysSinceLastActive === 0) {
            return;
        }

        // Consecutive day
        if ($daysSinceLastActive === 1) {
            $profile->current_streak++;
            if ($profile->current_streak > $profile->longest_streak) {
                $profile->longest_streak = $profile->current_streak;
            }

            // Check for streak milestones
            $this->checkStreakMilestones($user, $profile->current_streak);
        } else {
            // Streak broken
            $profile->current_streak = 1;
        }

        $profile->last_active_date = $today->toDateString();
        $profile->save();
    }

    /**
     * Check if user reached streak milestones and grant rewards
     */
    private function checkStreakMilestones(User $user, int $streak): void
    {
        $milestones = [7, 14, 30, 60, 90];

        if (in_array($streak, $milestones)) {
            $rewardsEngine = app(RewardsEngine::class);
            $reward = $rewardsEngine->generateStreakReward($user, $streak);

            if ($reward) {
                app(\App\Services\World\WorldService::class)->applyReward($user, $reward);
            }
        }
    }
}
