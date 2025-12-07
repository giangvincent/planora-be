<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LearningTaskStatus;
use App\Enums\LearningTaskType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class LearningTask extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'step_id',
        'title',
        'description',
        'type',
        'status',
        'order',
        'estimated_minutes',
        'due_date',
        'linked_task_id',
    ];

    protected $casts = [
        'type' => LearningTaskType::class,
        'status' => LearningTaskStatus::class,
        'order' => 'integer',
        'estimated_minutes' => 'integer',
        'due_date' => 'date',
    ];

    protected $attributes = [
        'status' => LearningTaskStatus::Pending,
        'type' => LearningTaskType::Study,
        'order' => 0,
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(PhaseStep::class, 'step_id');
    }

    public function linkedTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'linked_task_id');
    }

    public function autoChecks(): HasMany
    {
        return $this->hasMany(AutoCheck::class, 'learning_task_id');
    }
}
