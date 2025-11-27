<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GoalStatus;
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
class Goal extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Searchable;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'target_date',
        'status',
        'progress',
        'color',
    ];

    protected $casts = [
        'target_date' => 'date',
        'progress' => 'integer',
        'status' => GoalStatus::class,
    ];

    protected $attributes = [
        'status' => GoalStatus::Active->value,
        'progress' => 0,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function scopeStatus(Builder $query, GoalStatus|string|null $status): Builder
    {
        if ($status instanceof GoalStatus) {
            $status = $status->value;
        }

        return $status === null ? $query : $query->where('status', $status);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || $term === '') {
            return $query;
        }

        $likeTerm = '%'.Str::lower($term).'%';

        return $query->where(function ($builder) use ($likeTerm) {
            $builder
                ->whereRaw('LOWER(title) LIKE ?', [$likeTerm])
                ->orWhereRaw('LOWER(description) LIKE ?', [$likeTerm]);
        });
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'target_date' => optional($this->target_date)?->toDateString(),
            'user_id' => $this->user_id,
        ];
    }
}
