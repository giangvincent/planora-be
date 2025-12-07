<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\RoleSourceType;
use App\Enums\RoleStatus;
use App\Enums\RoleVisibility;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\RoadmapParserService;
use App\Services\RoadmapService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct(
        private RoadmapService $roadmapService,
        private RoadmapParserService $parserService
    ) {}

    public function index(Request $request)
    {
        $roles = Role::with([
            'phases.steps.learningTasks.autoChecks',
        ])->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'source_type' => ['nullable', Rule::in(RoleSourceType::values())],
            'source_meta' => 'nullable|array',
            'visibility' => ['nullable', Rule::in(RoleVisibility::values())],
            'status' => ['nullable', Rule::in(RoleStatus::values())],
            'estimated_duration_weeks' => 'nullable|integer|min:1',
        ]);

        $role = $this->roadmapService->createRole($request->user(), $validated);

        return response()->json([
            'role' => $role->fresh(),
        ], 201);
    }

    public function show(Request $request, Role $role)
    {
        $this->authorizeRole($request, $role);

        return response()->json([
            'role' => $role->load('phases.steps.learningTasks.autoChecks'),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorizeRole($request, $role);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'source_type' => ['sometimes', Rule::in(RoleSourceType::values())],
            'source_meta' => 'nullable|array',
            'visibility' => ['sometimes', Rule::in(RoleVisibility::values())],
            'status' => ['sometimes', Rule::in(RoleStatus::values())],
            'estimated_duration_weeks' => 'nullable|integer|min:1',
        ]);

        $role->update($validated);

        return response()->json([
            'role' => $role->fresh('phases.steps.learningTasks.autoChecks'),
        ]);
    }

    public function destroy(Request $request, Role $role)
    {
        $this->authorizeRole($request, $role);
        $role->delete();

        return response()->json([
            'message' => 'Role deleted',
        ], 204);
    }

    public function importFromAi(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required_without:outline|string',
            'roleTitle' => 'nullable|string|max:255',
            'context' => 'nullable|string',
            'outline' => 'nullable|array',
        ]);

        $parsed = $this->parserService->parse($validated['outline'] ?? $validated['prompt']);

        $role = $this->roadmapService->createRole($request->user(), [
            'title' => $validated['roleTitle'] ?? $parsed['title'] ?? 'Imported Role',
            'description' => $parsed['description'] ?? null,
            'source_type' => RoleSourceType::Ai->value,
            'source_meta' => [
                'prompt' => $validated['prompt'] ?? null,
                'context' => $validated['context'] ?? null,
                'raw_outline' => $validated['outline'] ?? null,
            ],
            'status' => RoleStatus::Active->value,
            'visibility' => RoleVisibility::Private->value,
        ]);

        $phaseCount = 0;
        $stepCount = 0;
        $taskCount = 0;

        foreach ($parsed['phases'] ?? [] as $phase) {
            $phaseModel = $this->roadmapService->addPhase($role, [
                'title' => $phase['title'] ?? 'Phase',
                'description' => $phase['description'] ?? null,
            ]);
            $phaseCount++;

            foreach ($phase['steps'] ?? [] as $step) {
                $stepModel = $this->roadmapService->addStep($phaseModel, [
                    'title' => $step['title'] ?? 'Step',
                    'description' => $step['description'] ?? null,
                ]);
                $stepCount++;

                foreach ($step['tasks'] ?? [] as $task) {
                    $this->roadmapService->addLearningTask($stepModel, [
                        'title' => $task['title'] ?? 'Task',
                        'description' => $task['description'] ?? null,
                        'type' => $task['type'] ?? null,
                    ]);
                    $taskCount++;
                }
            }
        }

        return response()->json([
            'roleId' => $role->id,
            'phasesCount' => $phaseCount,
            'stepsCount' => $stepCount,
            'tasksCount' => $taskCount,
        ], 201);
    }

    private function authorizeRole(Request $request, Role $role): void
    {
        if ($role->user_id === $request->user()->id) {
            return;
        }

        if ($role->visibility === RoleVisibility::Public) {
            return;
        }

        abort(403);
    }
}
