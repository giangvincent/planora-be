<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Services\Gamification\GamificationService;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function __construct(
        private GamificationService $gamificationService
    ) {}

    /**
     * Display a listing of goals
     */
    public function index(Request $request)
    {
        $query = $request->user()->goals();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $goals = $query->withCount('tasks')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'goals' => $goals,
        ]);
    }

    /**
     * Store a newly created goal
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'color' => 'nullable|string|max:32',
        ]);

        $goal = $request->user()->goals()->create($request->all());

        return response()->json([
            'goal' => $goal,
        ], 201);
    }

    /**
     * Display the specified goal
     */
    public function show(Request $request, Goal $goal)
    {
        $this->authorize('view', $goal);

        return response()->json([
            'goal' => $goal->load(['tasks']),
        ]);
    }

    /**
     * Update the specified goal
     */
    public function update(Request $request, Goal $goal)
    {
        $this->authorize('update', $goal);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'color' => 'nullable|string|max:32',
            'progress' => 'sometimes|integer|min:0|max:100',
            'status' => 'sometimes|in:active,completed,archived',
        ]);

        $goal->update($request->all());

        return response()->json([
            'goal' => $goal->fresh(['tasks']),
        ]);
    }

    /**
     * Remove the specified goal
     */
    public function destroy(Request $request, Goal $goal)
    {
        $this->authorize('delete', $goal);

        $goal->delete();

        return response()->json([
            'message' => 'Goal deleted successfully',
        ], 204);
    }

    /**
     * Mark goal as completed
     */
    public function complete(Request $request, Goal $goal)
    {
        $this->authorize('update', $goal);

        $goal->update([
            'status' => 'completed',
            'progress' => 100,
        ]);

        // Trigger gamification
        $this->gamificationService->onGoalCompleted($request->user(), $goal);

        return response()->json([
            'goal' => $goal->fresh(['tasks']),
            'gamification' => $request->user()->gamificationProfile,
        ]);
    }

    /**
     * Archive the goal
     */
    public function archive(Request $request, Goal $goal)
    {
        $this->authorize('update', $goal);

        $goal->update([
            'status' => 'archived',
        ]);

        return response()->json([
            'goal' => $goal->fresh(['tasks']),
        ]);
    }
}
