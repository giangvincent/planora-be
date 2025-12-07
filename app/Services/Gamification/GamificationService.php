<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use App\Models\GamificationProfile;
use App\Models\LearningTask;
use App\Models\Task;
use App\Models\Goal;
use App\Models\User;
use Carbon\Carbon;

class GamificationService
{
    public function __construct(
        private RewardsEngine $rewardsEngine,
        private StreakService $streakService
    ) {}

    /**
     * Handle task completion event
     */
    public function onTaskCompleted(User $user, Task $task): void
    {
        $profile = $this->getOrCreateProfile($user);

        // Calculate XP and coins based on task attributes
        $xp = $this->calculateTaskXP($task);
        $coins = $this->calculateTaskCoins($task);

        // Update profile
        $profile->xp += $xp;
        $profile->coins += $coins;
        $profile->level = $this->calculateLevel($profile->xp);
        $profile->save();

        // Check streak
        $this->streakService->checkAndUpdateStreak($user, now());

        // Generate world rewards
        $reward = $this->rewardsEngine->generateTaskReward($user, $task);
        if ($reward) {
            app(\App\Services\World\WorldService::class)->applyReward($user, $reward);
        }
    }

    /**
     * Handle goal completion event
     */
    public function onGoalCompleted(User $user, Goal $goal): void
    {
        $profile = $this->getOrCreateProfile($user);

        $xp = 500; // Base XP for goal completion
        $coins = 100;

        $profile->xp += $xp;
        $profile->coins += $coins;
        $profile->level = $this->calculateLevel($profile->xp);
        $profile->save();

        // Generate world rewards
        $reward = $this->rewardsEngine->generateGoalReward($user, $goal);
        if ($reward) {
            app(\App\Services\World\WorldService::class)->applyReward($user, $reward);
        }
    }

    /**
     * Handle focus session completion
     */
    public function onFocusSessionCompleted(User $user, int $minutes): void
    {
        $profile = $this->getOrCreateProfile($user);

        $xp = $minutes * 2;
        $coins = (int)($minutes / 10);

        $profile->xp += $xp;
        $profile->coins += $coins;
        $profile->level = $this->calculateLevel($profile->xp);
        $profile->save();
    }

    /**
     * Grant daily login reward
     */
    public function grantDailyLoginReward(User $user): void
    {
        $profile = $this->getOrCreateProfile($user);

        $profile->xp += 10;
        $profile->coins += 5;
        $profile->save();
    }

    /**
     * Bonus when a learner passes an auto-check
     */
    public function grantAutoCheckBonus(User $user): void
    {
        $profile = $this->getOrCreateProfile($user);

        $profile->xp += 20;
        $profile->coins += 10;
        $profile->save();
    }

    /**
     * Handle learning task completion (roadmaps)
     */
    public function onLearningTaskCompleted(User $user, LearningTask $learningTask): void
    {
        $profile = $this->getOrCreateProfile($user);

        $baseXp = 30;
        $typeBonus = match ((string) $learningTask->type) {
            'project' => 40,
            'practice' => 20,
            'quiz' => 15,
            default => 10,
        };

        $estimated = $learningTask->estimated_minutes ?? 0;
        $timeBonus = (int) floor($estimated / 15) * 5;

        $xp = $baseXp + $typeBonus + $timeBonus;
        $coins = (int) max(5, floor($xp / 10));

        $profile->xp += $xp;
        $profile->coins += $coins;
        $profile->level = $this->calculateLevel($profile->xp);
        $profile->save();

        $this->streakService->checkAndUpdateStreak($user, now());
    }

    /**
     * Get or create gamification profile for user
     */
    private function getOrCreateProfile(User $user): GamificationProfile
    {
        return $user->gamificationProfile()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'level' => 1,
                'xp' => 0,
                'coins' => 0,
                'current_streak' => 0,
                'longest_streak' => 0,
            ]
        );
    }

    /**
     * Calculate XP for task based on priority and time
     */
    private function calculateTaskXP(Task $task): int
    {
        $baseXP = match ($task->priority) {
            'high' => 50,
            'medium' => 30,
            'low' => 15,
            default => 20,
        };

        // Bonus for completing within estimated time
        if ($task->estimated_minutes && $task->actual_minutes) {
            if ($task->actual_minutes <= $task->estimated_minutes) {
                $baseXP += 10;
            }
        }

        return $baseXP;
    }

    /**
     * Calculate coins for task
     */
    private function calculateTaskCoins(Task $task): int
    {
        return match ($task->priority) {
            'high' => 25,
            'medium' => 15,
            'low' => 10,
            default => 10,
        };
    }

    /**
     * Calculate level from XP
     */
    private function calculateLevel(int $xp): int
    {
        // Simple formula: level = floor(sqrt(xp / 100)) + 1
        return (int)floor(sqrt($xp / 100)) + 1;
    }
}
