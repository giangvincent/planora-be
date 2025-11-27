<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class CalendarEntry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'task_id',
        'start_at',
        'end_at',
        'all_day',
        'is_generated',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'all_day' => 'boolean',
        'is_generated' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function scopeForRange(Builder $query, ?string $start, ?string $end): Builder
    {
        if ($start !== null) {
            $query->where('start_at', '>=', $start);
        }

        if ($end !== null) {
            $query->where(function (Builder $builder) use ($end) {
                $builder
                    ->whereNull('end_at')
                    ->orWhere('end_at', '<=', $end);
            });
        }

        return $query;
    }
}
