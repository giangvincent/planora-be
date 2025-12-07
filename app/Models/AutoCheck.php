<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AutoCheckType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class AutoCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_task_id',
        'type',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
        'type' => AutoCheckType::class,
    ];

    public function learningTask(): BelongsTo
    {
        return $this->belongsTo(LearningTask::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(AutoCheckResult::class);
    }
}
