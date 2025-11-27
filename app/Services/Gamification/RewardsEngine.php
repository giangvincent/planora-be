<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use App\Models\Task;
use App\Models\Goal;
use App\Models\User;

class RewardsEngine
{
    /**
     * Generate reward for task completion
     */
    public function generateTaskReward(User $user, Task $task): ?WorldReward
    {
        $profile = $user->gamificationProfile;

        // First task of the day - random common decoration
        $tasksToday = $user->tasks()
            ->where('status', 'done')
            ->whereDate('updated_at', now()->toDateString())
            ->count();

        if ($tasksToday === 1) {
            return new WorldReward(
                type: 'object',
                key: 'tree_basic',
                rarity: 'common'
            );
        }

        // Every 10 tasks - rare object
        if ($profile && $profile->xp % 500 === 0) {
            return new WorldReward(
                type: 'object',
                key: 'lamp_vintage',
                rarity: 'rare'
            );
        }

        return null;
    }

    /**
     * Generate reward for goal completion
     */
    public function generateGoalReward(User $user, Goal $goal): ?WorldReward
    {
        // Goal completion always grants a rare item
        return new WorldReward(
            type: 'object',
            key: 'fountain',
            rarity: 'rare'
        );
    }

    /**
     * Generate reward for streak milestone
     */
    public function generateStreakReward(User $user, int $streakDays): ?WorldReward
    {
        if ($streakDays === 7) {
            return new WorldReward(
                type: 'object',
                key: 'pet_cat',
                rarity: 'epic'
            );
        }

        if ($streakDays === 30) {
            return new WorldReward(
                type: 'theme',
                key: 'theme_forest',
                rarity: 'epic'
            );
        }

        return null;
    }
}

/**
 * DTO for world rewards
 */
class WorldReward
{
    public function __construct(
        public string $type,
        public string $key,
        public string $rarity
    ) {}
}
