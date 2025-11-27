<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GoalService
{
    /**
     * @param array{
     *     status?: GoalStatus|string|null,
     *     q?: string|null,
     *     sort?: string|null,
     *     sort_dir?: string|null,
     *     per_page?: int|null,
     * } $filters
     */
    public function paginate(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Goal::query()
            ->where('user_id', $user->id)
            ->status($filters['status'] ?? null)
            ->search($filters['q'] ?? null);

        $sortField = $this->determineSortField($filters['sort'] ?? null);
        $sortDirection = strtolower((string) ($filters['sort_dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortField, $sortDirection)
            ->orderBy('id', 'desc');

        $perPage = $this->sanitizePerPage($filters['per_page'] ?? null);

        return $query->paginate($perPage)->appends($filters);
    }

    private function determineSortField(?string $sort): string
    {
        return match ($sort) {
            'targetDate' => 'target_date',
            'progress' => 'progress',
            default => 'created_at',
        };
    }

    private function sanitizePerPage(?int $perPage): int
    {
        return max(1, min($perPage ?? 20, 100));
    }
}
