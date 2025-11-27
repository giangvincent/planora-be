<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\GoalStatus;
use App\Http\Requests\Goal\StoreGoalRequest;
use App\Http\Requests\Goal\UpdateGoalRequest;
use App\Http\Resources\GoalResource;
use App\Models\Goal;
use App\Services\GoalService;
use Illuminate\Http\JsonResponse;

class GoalController extends ApiController
{
    public function __construct(private readonly GoalService $goalService)
    {
    }

    public function index(): JsonResponse
    {
        $filters = request()->only(['status', 'q', 'sort', 'sort_dir', 'per_page']);
        $paginator = $this->goalService->paginate(request()->user(), $filters);

        return $this->respondWithPagination($paginator, GoalResource::class);
    }

    public function store(StoreGoalRequest $request): JsonResponse
    {
        $this->authorize('create', Goal::class);

        $goal = $request->user()->goals()->create($request->payload());

        return $this->respond(GoalResource::make($goal)->resolve(), [], 201);
    }

    public function show(Goal $goal): JsonResponse
    {
        $this->authorize('view', $goal);

        return $this->respond(GoalResource::make($goal)->resolve());
    }

    public function update(UpdateGoalRequest $request, Goal $goal): JsonResponse
    {
        $this->authorize('update', $goal);

        $payload = $request->payload();
        $goal->fill($payload)->save();

        return $this->respond(GoalResource::make($goal->fresh())->resolve());
    }

    public function destroy(Goal $goal): JsonResponse
    {
        $this->authorize('delete', $goal);

        $goal->delete();

        return response()->json([], 204);
    }

    public function complete(Goal $goal): JsonResponse
    {
        $this->authorize('update', $goal);

        $goal->fill([
            'status' => GoalStatus::Completed->value,
            'progress' => 100,
        ])->save();

        return $this->respond(GoalResource::make($goal->fresh())->resolve());
    }

    public function archive(Goal $goal): JsonResponse
    {
        $this->authorize('update', $goal);

        $goal->fill([
            'status' => GoalStatus::Archived->value,
        ])->save();

        return $this->respond(GoalResource::make($goal->fresh())->resolve());
    }
}
