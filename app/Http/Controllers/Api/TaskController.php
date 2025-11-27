<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\TaskStatus;
use App\Http\Requests\Task\BulkTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Goal;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TaskController extends ApiController
{
    public function __construct(private readonly TaskService $taskService)
    {
    }

    public function index(): JsonResponse
    {
        $filters = request()->only(['status', 'goal_id', 'goalId', 'due_date', 'dueDate', 'week', 'priority', 'q', 'sort', 'sort_dir', 'per_page']);
        $normalized = $this->normalizeFilters($filters);
        $paginator = $this->taskService->paginate(request()->user(), $normalized);

        return $this->respondWithPagination($paginator, TaskResource::class);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $this->authorize('create', Task::class);

        $payload = $this->prepareTaskPayload($request->payload(), $request->user()->id);

        $task = $request->user()->tasks()->create($payload);

        return $this->respond(TaskResource::make($task->fresh(['goal']))->resolve(), [], 201);
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return $this->respond(TaskResource::make($task->load('goal'))->resolve());
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $payload = $this->prepareTaskPayload($request->payload(), $request->user()->id, allowEmpty: true);
        $task->fill($payload)->save();

        return $this->respond(TaskResource::make($task->fresh(['goal']))->resolve());
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json([], 204);
    }

    public function complete(Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task->fill([
            'status' => TaskStatus::Done->value,
        ])->save();

        return $this->respond(TaskResource::make($task->fresh(['goal']))->resolve());
    }

    public function skip(Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task->fill([
            'status' => TaskStatus::Skipped->value,
        ])->save();

        return $this->respond(TaskResource::make($task->fresh(['goal']))->resolve());
    }

    public function bulk(BulkTaskRequest $request): JsonResponse
    {
        $user = $request->user();

        $created = [];
        $updated = [];
        $deleted = [];

        DB::transaction(function () use ($request, $user, &$created, &$updated, &$deleted): void {
            foreach ($request->createPayloads() as $payload) {
                $task = $user->tasks()->create($this->prepareTaskPayload($payload, $user->id));
                $created[] = TaskResource::make($task)->resolve();
            }

            foreach ($request->updatePayloads() as $updatePayload) {
                /** @var Task $task */
                $task = $user->tasks()->findOrFail($updatePayload['id']);
                $task->fill($this->prepareTaskPayload($updatePayload['attributes'], $user->id, allowEmpty: true))->save();
                $updated[] = TaskResource::make($task->fresh())->resolve();
            }

            foreach ($request->deletions() as $taskId) {
                $task = $user->tasks()->find($taskId);
                if ($task) {
                    $task->delete();
                    $deleted[] = $taskId;
                }
            }
        });

        return $this->respond([
            'created' => $created,
            'updated' => $updated,
            'deleted' => $deleted,
        ]);
    }

    private function normalizeFilters(array $filters): array
    {
        $normalized = [];

        if (isset($filters['status'])) {
            $normalized['status'] = $filters['status'];
        }

        $normalized['goal_id'] = $filters['goalId'] ?? $filters['goal_id'] ?? null;
        $normalized['due_date'] = $filters['dueDate'] ?? $filters['due_date'] ?? null;
        $normalized['week'] = $filters['week'] ?? null;
        $normalized['priority'] = $filters['priority'] ?? null;
        $normalized['q'] = $filters['q'] ?? null;
        $normalized['sort'] = $filters['sort'] ?? null;
        $normalized['sort_dir'] = $filters['sort_dir'] ?? null;
        $normalized['per_page'] = isset($filters['per_page']) ? (int) $filters['per_page'] : null;

        return Arr::where($normalized, fn ($value) => $value !== null && $value !== '');
    }

    private function prepareTaskPayload(array $payload, int $userId, bool $allowEmpty = false): array
    {
        if (isset($payload['goal_id'])) {
            $goalId = (int) $payload['goal_id'];
            if (! $this->goalBelongsToUser($goalId, $userId)) {
                throw (new ModelNotFoundException())->setModel(Goal::class, $goalId);
            }
        }

        if (! $allowEmpty) {
            $payload['user_id'] = $userId;
        }

        return $payload;
    }

    private function goalBelongsToUser(int $goalId, int $userId): bool
    {
        return Goal::query()->where('user_id', $userId)->where('id', $goalId)->exists();
    }
}
