<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\DifficultyLevel;
use App\Http\Controllers\Controller;
use App\Models\PhaseStep;
use App\Models\RolePhase;
use App\Services\RoadmapService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PhaseStepController extends Controller
{
    public function __construct(
        private RoadmapService $roadmapService
    ) {}

    public function store(Request $request, RolePhase $phase)
    {
        $this->authorizeRole($request, $phase);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'difficulty_level' => ['nullable', Rule::in(DifficultyLevel::values())],
        ]);

        $step = $this->roadmapService->addStep($phase, $validated);

        return response()->json([
            'step' => $step,
        ], 201);
    }

    public function update(Request $request, PhaseStep $step)
    {
        $this->authorizeRole($request, $step->phase);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'difficulty_level' => ['nullable', Rule::in(DifficultyLevel::values())],
        ]);

        $step->update($validated);

        return response()->json([
            'step' => $step->fresh('learningTasks'),
        ]);
    }

    public function destroy(Request $request, PhaseStep $step)
    {
        $this->authorizeRole($request, $step->phase);
        $step->delete();

        return response()->json([
            'message' => 'Step deleted',
        ], 204);
    }

    private function authorizeRole(Request $request, RolePhase $phase): void
    {
        abort_unless($phase->role->user_id === $request->user()->id, 403);
    }
}
