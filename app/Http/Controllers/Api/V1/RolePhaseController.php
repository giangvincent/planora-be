<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePhase;
use App\Services\RoadmapService;
use Illuminate\Http\Request;

class RolePhaseController extends Controller
{
    public function __construct(
        private RoadmapService $roadmapService
    ) {}

    public function store(Request $request, Role $role)
    {
        $this->authorizeRole($request, $role);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'estimated_duration_weeks' => 'nullable|integer|min:1',
        ]);

        $phase = $this->roadmapService->addPhase($role, $validated);

        return response()->json([
            'phase' => $phase,
        ], 201);
    }

    public function update(Request $request, RolePhase $phase)
    {
        $this->authorizeRole($request, $phase->role);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'estimated_duration_weeks' => 'nullable|integer|min:1',
        ]);

        $phase->update($validated);

        return response()->json([
            'phase' => $phase->fresh('steps.learningTasks'),
        ]);
    }

    public function destroy(Request $request, RolePhase $phase)
    {
        $this->authorizeRole($request, $phase->role);
        $phase->delete();

        return response()->json([
            'message' => 'Phase deleted',
        ], 204);
    }

    private function authorizeRole(Request $request, Role $role): void
    {
        abort_unless($role->user_id === $request->user()->id, 403);
    }
}
