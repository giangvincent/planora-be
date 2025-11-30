<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    /**
     * Get user's gamification profile
     */
    public function profile(Request $request)
    {
        $profile = $request->user()->gamificationProfile()
            ->firstOrCreate(
                ['user_id' => $request->user()->id],
                [
                    'level' => 1,
                    'xp' => 0,
                    'coins' => 0,
                    'current_streak' => 0,
                    'longest_streak' => 0,
                ]
            );

        // Get stats
        $tasksCompleted = $request->user()->tasks()->where('status', 'done')->count();
        $goalsCompleted = $request->user()->goals()->where('status', 'completed')->count();
        $achievementsUnlocked = $request->user()->achievements()->count();

        return response()->json([
            'profile' => $profile,
            'stats' => [
                'tasks_completed' => $tasksCompleted,
                'goals_completed' => $goalsCompleted,
                'achievements_unlocked' => $achievementsUnlocked,
            ],
        ]);
    }

    /**
     * Get all available achievements
     */
    public function achievements()
    {
        $achievements = Achievement::all();

        return response()->json([
            'achievements' => $achievements,
        ]);
    }

    /**
     * Get user's unlocked achievements
     */
    public function unlockedAchievements(Request $request)
    {
        $achievements = $request->user()
            ->achievements()
            ->withPivot('unlocked_at', 'meta')
            ->get();

        return response()->json([
            'achievements' => $achievements,
        ]);
    }
}
