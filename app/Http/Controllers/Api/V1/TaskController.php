<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\Gamification\GamificationService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private GamificationService $gamificationService
    ) {}

    /**
     * Display a listing of tasks
     */
    public function index(Request $request)
    {
        $query = $request->user()->tasks();

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('goal_id')) {
            $query->where('goal_id', $request->goal_id);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('date')) {
            $query->whereDate('due_date', $request->date);
        }

        if ($request->has('week')) {
            // Get tasks for the week containing the specified date
            $date = \Carbon\Carbon::parse($request->week);
            $query->whereBetween('due_date', [
                $date->startOfWeek(),
                $date->endOfWeek(),
            ]);
        }

        $tasks = $query->with('goal')->orderBy('due_at')->get();

        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    /**
     * Store a newly created task
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'goal_id' => 'nullable|exists:goals,id',
            'priority' => 'sometimes|in:low,medium,high',
            'estimated_minutes' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date',
            'due_at' => 'nullable|date',
            'all_day' => 'sometimes|boolean',
            'repeat_rule' => 'nullable|string',
        ]);

        $task = $request->user()->tasks()->create($request->all());

        return response()->json([
            'task' => $task->load('goal'),
        ], 201);
    }

    /**
     * Display the specified task
     */
    public function show(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        return response()->json([
            'task' => $task->load('goal'),
        ]);
    }

    /**
     * Update the specified task
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'notes' => 'nullable|string',
            'goal_id' => 'nullable|exists:goals,id',
            'priority' => 'sometimes|in:low,medium,high',
            'estimated_minutes' => 'nullable|integer|min:1',
            'actual_minutes' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date',
            'due_at' => 'nullable|date',
            'all_day' => 'sometimes|boolean',
            'repeat_rule' => 'nullable|string',
            'status' => 'sometimes|in:pending,done,skipped',
        ]);

        $task->update($request->all());

        return response()->json([
            'task' => $task->fresh(['goal']),
        ]);
    }

    /**
     * Remove the specified task
     */
    public function destroy(Request $request, Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ], 204);
    }

    /**
     * Mark task as completed
     */
    public function complete(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'actual_minutes' => 'nullable|integer|min:1',
        ]);

        $task->update([
            'status' => 'done',
            'actual_minutes' => $request->actual_minutes,
        ]);

        // Trigger gamification
        $this->gamificationService->onTaskCompleted($request->user(), $task);

        return response()->json([
            'task' => $task->fresh(['goal']),
            'gamification' => $request->user()->gamificationProfile,
        ]);
    }

    /**
     * Mark task as skipped
     */
    public function skip(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update([
            'status' => 'skipped',
        ]);

        return response()->json([
            'task' => $task->fresh(['goal']),
        ]);
    }
}
