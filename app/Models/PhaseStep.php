<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DifficultyLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class PhaseStep extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'phase_id',
        'title',
        'description',
        'order',
        'difficulty_level',
    ];

    protected $casts = [
        'difficulty_level' => DifficultyLevel::class,
        'order' => 'integer',
    ];

    public function phase(): BelongsTo
    {
        return $this->belongsTo(RolePhase::class, 'phase_id');
    }

    public function learningTasks(): HasMany
    {
        return $this->hasMany(LearningTask::class, 'step_id');
    }
}
