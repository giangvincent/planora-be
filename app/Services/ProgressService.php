<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LearningTaskStatus;
use App\Models\LearningTask;
use App\Models\PhaseStep;
use App\Models\Role;
use App\Models\RolePhase;
use App\Models\RoleProgressSnapshot;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ProgressService
{
    /**
     * Recalculate progress for a role and optionally create a snapshot.
     */
    public function updateForRole(Role $role, User $user, bool $snapshot = false): array
    {
        $taskQuery = LearningTask::query()
            ->whereHas('step.phase', fn ($query) => $query->where('role_id', $role->id));

        $totalTasks = (clone $taskQuery)->count();
        $completedTasks = (clone $taskQuery)->where('status', LearningTaskStatus::Completed->value)->count();

        $steps = PhaseStep::with('learningTasks')
            ->whereHas('phase', fn ($query) => $query->where('role_id', $role->id))
            ->get();

        $completedSteps = $this->countCompletedSteps($steps);

        $phases = RolePhase::with('steps.learningTasks')
            ->where('role_id', $role->id)
            ->get();

        $completedPhases = $this->countCompletedPhases($phases);

        if ($snapshot) {
            $this->snapshot($role, $user, [
                'completed_tasks_count' => $completedTasks,
                'total_tasks_count' => $totalTasks,
                'completed_steps_count' => $completedSteps,
                'completed_phases_count' => $completedPhases,
            ]);
        }

        return [
            'completed_tasks_count' => $completedTasks,
            'total_tasks_count' => $totalTasks,
            'completed_steps_count' => $completedSteps,
            'completed_phases_count' => $completedPhases,
        ];
    }

    public function snapshot(Role $role, User $user, array $data): RoleProgressSnapshot
    {
        return RoleProgressSnapshot::create([
            'role_id' => $role->id,
            'user_id' => $user->id,
            'completed_tasks_count' => $data['completed_tasks_count'],
            'total_tasks_count' => $data['total_tasks_count'],
            'completed_steps_count' => $data['completed_steps_count'],
            'completed_phases_count' => $data['completed_phases_count'],
            'snapshot_date' => CarbonImmutable::now()->toDateString(),
        ]);
    }

    public function dashboardStats(User $user): array
    {
        $roles = Role::with(['phases.steps.learningTasks'])
            ->where('user_id', $user->id)
            ->get();

        return $roles->map(fn (Role $role) => [
            'role_id' => $role->id,
            'title' => $role->title,
            'progress' => $this->updateForRole($role, $user),
        ])->all();
    }

    private function countCompletedSteps(Collection $steps): int
    {
        return $steps->filter(function (PhaseStep $step): bool {
            if ($step->learningTasks->isEmpty()) {
                return false;
            }

            return $step->learningTasks->every(
                fn (LearningTask $task): bool => $task->status === LearningTaskStatus::Completed
            );
        })->count();
    }

    private function countCompletedPhases(Collection $phases): int
    {
        return $phases->filter(function (RolePhase $phase): bool {
            if ($phase->steps->isEmpty()) {
                return false;
            }

            return $phase->steps->every(function (PhaseStep $step): bool {
                if ($step->learningTasks->isEmpty()) {
                    return false;
                }

                return $step->learningTasks->every(
                    fn (LearningTask $task): bool => $task->status === LearningTaskStatus::Completed
                );
            });
        })->count();
    }
}
