<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LearningTaskStatus;
use App\Enums\RoleSourceType;
use App\Enums\RoleStatus;
use App\Models\LearningTask;
use App\Models\PhaseStep;
use App\Models\Role;
use App\Models\RolePhase;
use App\Models\Task;
use App\Models\User;
use App\Services\Gamification\GamificationService;
use Illuminate\Auth\Access\AuthorizationException;

class RoadmapService
{
    public function __construct(
        private GamificationService $gamificationService,
        private ProgressService $progressService
    ) {}

    /**
     * @param array{
     *     title: string,
     *     slug?: string|null,
     *     description?: string|null,
     *     source_type?: string|null,
     *     source_meta?: array|null,
     *     visibility?: string|null,
     *     status?: string|null,
     *     estimated_duration_weeks?: int|null,
     * } $data
     */
    public function createRole(User $user, array $data): Role
    {
        $payload = array_merge($data, ['user_id' => $user->id]);
        $payload['source_type'] ??= RoleSourceType::Manual->value;
        $payload['status'] ??= RoleStatus::Draft->value;

        return Role::create($payload);
    }

    /**
     * @param array{
     *     title: string,
     *     description?: string|null,
     *     order?: int|null,
     *     estimated_duration_weeks?: int|null,
     * } $data
     */
    public function addPhase(Role $role, array $data): RolePhase
    {
        $order = $data['order'] ?? ($role->phases()->max('order') + 1);

        return $role->phases()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'order' => $order ?? 0,
            'estimated_duration_weeks' => $data['estimated_duration_weeks'] ?? null,
        ]);
    }

    /**
     * @param array{
     *     title: string,
     *     description?: string|null,
     *     order?: int|null,
     *     difficulty_level?: string|null,
     * } $data
     */
    public function addStep(RolePhase $phase, array $data): PhaseStep
    {
        $order = $data['order'] ?? ($phase->steps()->max('order') + 1);

        return $phase->steps()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'order' => $order ?? 0,
            'difficulty_level' => $data['difficulty_level'] ?? null,
        ]);
    }

    /**
     * @param array{
     *     title: string,
     *     description?: string|null,
     *     type?: string|null,
     *     status?: string|null,
     *     order?: int|null,
     *     estimated_minutes?: int|null,
     *     due_date?: string|null,
     *     linked_task_id?: int|null,
     * } $data
     */
    public function addLearningTask(PhaseStep $step, array $data): LearningTask
    {
        $order = $data['order'] ?? ($step->learningTasks()->max('order') + 1);

        return $step->learningTasks()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? null,
            'status' => $data['status'] ?? null,
            'order' => $order ?? 0,
            'estimated_minutes' => $data['estimated_minutes'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'linked_task_id' => $data['linked_task_id'] ?? null,
        ]);
    }

    public function syncToPlanner(LearningTask $learningTask, User $user, array $overrides = []): Task
    {
        if ($learningTask->step->phase->role->user_id !== $user->id) {
            throw new AuthorizationException();
        }

        $payload = [
            'user_id' => $user->id,
            'title' => $overrides['title'] ?? $learningTask->title,
            'notes' => $overrides['notes'] ?? $learningTask->description,
            'estimated_minutes' => $overrides['estimated_minutes'] ?? $learningTask->estimated_minutes,
            'due_date' => $overrides['due_date'] ?? $learningTask->due_date,
            'status' => $overrides['status'] ?? null,
            'goal_id' => $overrides['goal_id'] ?? null,
            'priority' => $overrides['priority'] ?? null,
        ];

        $filtered = array_filter($payload, static fn ($value) => $value !== null);

        /** @var Task $task */
        $task = $learningTask->linked_task_id
            ? tap(Task::where('user_id', $user->id)->findOrFail($learningTask->linked_task_id))->update($filtered)
            : Task::create($filtered);

        if (! $learningTask->linked_task_id) {
            $learningTask->update(['linked_task_id' => $task->id]);
        }

        return $task->fresh();
    }

    public function markTaskCompleted(LearningTask $learningTask, User $user, ?int $actualMinutes = null): LearningTask
    {
        if ($learningTask->step->phase->role->user_id !== $user->id) {
            throw new AuthorizationException();
        }

        $learningTask->update(['status' => LearningTaskStatus::Completed]);

        if ($learningTask->linked_task_id) {
            $task = $learningTask->linkedTask;
            if ($task) {
                $task->update([
                    'status' => \App\Enums\TaskStatus::Done,
                    'actual_minutes' => $actualMinutes,
                ]);
            }
        }

        $this->gamificationService->onLearningTaskCompleted($user, $learningTask);
        $this->progressService->updateForRole($learningTask->step->phase->role, $user);

        return $learningTask->fresh([
            'step.phase.role',
            'linkedTask',
        ]);
    }
}
