<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    /**
     * @param array{
     *     status?: TaskStatus|string|null,
     *     goal_id?: int|null,
     *     due_date?: string|null,
     *     week?: string|null,
     *     priority?: TaskPriority|string|null,
     *     q?: string|null,
     *     sort?: string|null,
     *     sort_dir?: string|null,
     *     per_page?: int|null,
     * } $filters
     */
    public function paginate(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Task::query()
            ->where('user_id', $user->id)
            ->status($filters['status'] ?? null)
            ->forGoal(isset($filters['goal_id']) ? (int) $filters['goal_id'] : null)
            ->priority($filters['priority'] ?? null)
            ->search($filters['q'] ?? null)
            ->forWeek($filters['week'] ?? null);

        if (! empty($filters['due_date'])) {
            $dueDate = $filters['due_date'];
            $query->where(function (Builder $builder) use ($dueDate): void {
                $builder
                    ->whereDate('due_at', $dueDate)
                    ->orWhere('due_date', $dueDate);
            });
        }

        $sortField = $this->determineSortField($filters['sort'] ?? null);
        $sortDirection = strtolower((string) ($filters['sort_dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortField, $sortDirection)
            ->orderBy('id', 'desc');

        $perPage = $this->sanitizePerPage($filters['per_page'] ?? null);

        return $query->paginate($perPage)->appends($filters);
    }

    private function determineSortField(?string $sort): string
    {
        return match ($sort) {
            'dueAt' => 'due_at',
            'priority' => 'priority',
            default => 'due_at',
        };
    }

    private function sanitizePerPage(?int $perPage): int
    {
        return max(1, min($perPage ?? 20, 100));
    }
}
