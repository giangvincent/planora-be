<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CalendarEntry;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CalendarService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEntries(User $user, ?string $start, ?string $end): array
    {
        $startAt = $this->parseDate($start);
        $endAt = $this->parseDate($end);

        $tasks = $this->tasksWithinRange($user, $startAt, $endAt);
        $entries = $this->calendarEntriesWithinRange($user, $startAt, $endAt);

        return $tasks
            ->merge($entries)
            ->sortBy('startAt')
            ->values()
            ->all();
    }

    private function tasksWithinRange(User $user, ?CarbonImmutable $start, ?CarbonImmutable $end): Collection
    {
        $query = Task::query()->where('user_id', $user->id);

        if ($start) {
            $query->where(function (Builder $builder) use ($start): void {
                $builder
                    ->where('due_at', '>=', $start)
                    ->orWhere(function (Builder $dateBuilder) use ($start): void {
                        $dateBuilder
                            ->whereNotNull('due_date')
                            ->where('due_date', '>=', $start->toDateString());
                    });
            });
        }

        if ($end) {
            $query->where(function (Builder $builder) use ($end): void {
                $builder
                    ->where('due_at', '<=', $end)
                    ->orWhere(function (Builder $dateBuilder) use ($end): void {
                        $dateBuilder
                            ->whereNotNull('due_date')
                            ->where('due_date', '<=', $end->toDateString());
                    });
            });
        }

        return $query->get()->map(function (Task $task) use ($user): array {
            $startAt = $task->due_at
                ? CarbonImmutable::parse($task->due_at)->setTimezone($user->timezone)
                : ($task->due_date ? CarbonImmutable::parse($task->due_date->toDateString(), $user->timezone) : null);

            if (! $startAt) {
                return null;
            }

            $endAt = $task->due_at
                ? CarbonImmutable::parse($task->due_at)->setTimezone($user->timezone)
                : $startAt->endOfDay();

            return [
                'type' => 'task',
                'id' => $task->id,
                'title' => $task->title,
                'startAt' => $startAt->toIso8601String(),
                'endAt' => $endAt->toIso8601String(),
                'allDay' => $task->all_day,
                'taskId' => $task->id,
                'goalId' => $task->goal_id,
                'isGenerated' => true,
            ];
        })->filter();
    }

    private function calendarEntriesWithinRange(User $user, ?CarbonImmutable $start, ?CarbonImmutable $end): Collection
    {
        $query = CalendarEntry::query()
            ->where('user_id', $user->id)
            ->forRange($start?->toIso8601String(), $end?->toIso8601String());

        if ($start) {
            $query->where('start_at', '>=', $start);
        }

        if ($end) {
            $query->where(function (Builder $builder) use ($end): void {
                $builder
                    ->whereNull('end_at')
                    ->orWhere('end_at', '<=', $end);
            });
        }

        return $query->get()->map(function (CalendarEntry $entry) use ($user): array {
            $startAt = CarbonImmutable::parse($entry->start_at)->setTimezone($user->timezone);
            $endAt = $entry->end_at
                ? CarbonImmutable::parse($entry->end_at)->setTimezone($user->timezone)
                : ($entry->all_day ? $startAt->endOfDay() : $startAt);

            return [
                'type' => 'calendar_entry',
                'id' => $entry->id,
                'title' => $entry->task?->title,
                'startAt' => $startAt->toIso8601String(),
                'endAt' => $endAt->toIso8601String(),
                'allDay' => $entry->all_day,
                'taskId' => $entry->task_id,
                'goalId' => $entry->task?->goal_id,
                'isGenerated' => $entry->is_generated,
            ];
        });
    }

    private function parseDate(?string $value): ?CarbonImmutable
    {
        if (! $value) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
