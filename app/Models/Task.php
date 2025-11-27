<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Searchable;

    protected $fillable = [
        'user_id',
        'goal_id',
        'title',
        'notes',
        'status',
        'priority',
        'estimated_minutes',
        'actual_minutes',
        'due_date',
        'due_at',
        'all_day',
        'repeat_rule',
    ];

    protected $casts = [
        'due_date' => 'date',
        'due_at' => 'datetime',
        'all_day' => 'boolean',
        'estimated_minutes' => 'integer',
        'actual_minutes' => 'integer',
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
    ];

    protected $attributes = [
        'status' => TaskStatus::Pending->value,
        'priority' => TaskPriority::Medium->value,
        'all_day' => false,
    ];

    protected $touches = [
        'goal',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function calendarEntries(): HasMany
    {
        return $this->hasMany(CalendarEntry::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function scopeStatus(Builder $query, TaskStatus|string|null $status): Builder
    {
        if ($status instanceof TaskStatus) {
            $status = $status->value;
        }

        return $status === null ? $query : $query->where('status', $status);
    }

    public function scopePriority(Builder $query, TaskPriority|string|null $priority): Builder
    {
        if ($priority instanceof TaskPriority) {
            $priority = $priority->value;
        }

        return $priority === null ? $query : $query->where('priority', $priority);
    }

    public function scopeForGoal(Builder $query, ?int $goalId): Builder
    {
        return $goalId === null ? $query : $query->where('goal_id', $goalId);
    }

    public function scopeDateRange(Builder $query, ?string $start, ?string $end, string $column = 'due_at'): Builder
    {
        if ($start !== null) {
            $query->where($column, '>=', $start);
        }

        if ($end !== null) {
            $query->where($column, '<=', $end);
        }

        return $query;
    }

    public function scopeForWeek(Builder $query, ?string $isoWeek): Builder
    {
        if (! $isoWeek) {
            return $query;
        }

        try {
            [$year, $week] = explode('-W', strtoupper($isoWeek));
            $startOfWeek = CarbonImmutable::now()->setISODate((int) $year, (int) $week)->startOfWeek();
            $endOfWeek = $startOfWeek->endOfWeek();
        } catch (\Throwable) {
            return $query;
        }

        return $query->whereBetween('due_at', [$startOfWeek, $endOfWeek]);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || $term === '') {
            return $query;
        }

        $likeTerm = '%'.Str::lower($term).'%';

        return $query->where(function (Builder $builder) use ($likeTerm) {
            $builder
                ->whereRaw('LOWER(title) LIKE ?', [$likeTerm])
                ->orWhereRaw('LOWER(notes) LIKE ?', [$likeTerm]);
        });
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'notes' => $this->notes,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_at' => optional($this->due_at)?->toIso8601String(),
            'user_id' => $this->user_id,
            'goal_id' => $this->goal_id,
        ];
    }
}
