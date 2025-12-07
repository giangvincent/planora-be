<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\LearningTaskStatus;
use App\Enums\LearningTaskType;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\LearningTask;
use App\Models\PhaseStep;
use App\Services\RoadmapService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LearningTaskController extends Controller
{
    public function __construct(
        private RoadmapService $roadmapService
    ) {}

    public function store(Request $request, PhaseStep $step)
    {
        $this->authorizeStep($request, $step);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['nullable', Rule::in(LearningTaskType::values())],
            'status' => ['nullable', Rule::in(LearningTaskStatus::values())],
            'order' => 'nullable|integer|min:0',
            'estimated_minutes' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date',
            'linked_task_id' => 'nullable|exists:tasks,id',
        ]);

        $task = $this->roadmapService->addLearningTask($step, $validated);

        return response()->json([
            'learning_task' => $task,
        ], 201);
    }

    public function update(Request $request, LearningTask $learningTask)
    {
        $this->authorizeStep($request, $learningTask->step);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => ['sometimes', Rule::in(LearningTaskType::values())],
            'status' => ['sometimes', Rule::in(LearningTaskStatus::values())],
            'order' => 'nullable|integer|min:0',
            'estimated_minutes' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date',
            'linked_task_id' => 'nullable|exists:tasks,id',
        ]);

        $learningTask->update($validated);

        return response()->json([
            'learning_task' => $learningTask->fresh('autoChecks'),
        ]);
    }

    public function destroy(Request $request, LearningTask $learningTask)
    {
        $this->authorizeStep($request, $learningTask->step);
        $learningTask->delete();

        return response()->json([
            'message' => 'Learning task deleted',
        ], 204);
    }

    public function complete(Request $request, LearningTask $learningTask)
    {
        $this->authorizeStep($request, $learningTask->step);

        $validated = $request->validate([
            'actual_minutes' => 'nullable|integer|min:1',
        ]);

        $task = $this->roadmapService->markTaskCompleted($learningTask, $request->user(), $validated['actual_minutes'] ?? null);

        return response()->json([
            'learning_task' => $task,
            'gamification' => $request->user()->gamificationProfile,
        ]);
    }

    public function syncTask(Request $request, LearningTask $learningTask)
    {
        $this->authorizeStep($request, $learningTask->step);

        $validated = $request->validate([
            'goal_id' => 'nullable|exists:goals,id',
            'priority' => ['nullable', Rule::in(TaskPriority::values())],
            'status' => ['nullable', Rule::in(TaskStatus::values())],
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'estimated_minutes' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date',
        ]);

        $task = $this->roadmapService->syncToPlanner($learningTask, $request->user(), $validated);

        return response()->json([
            'learning_task' => $learningTask->fresh(),
            'task' => $task,
        ]);
    }

    private function authorizeStep(Request $request, PhaseStep $step): void
    {
        abort_unless($step->phase->role->user_id === $request->user()->id, 403);
    }
}
