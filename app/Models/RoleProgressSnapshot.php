<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RoleProgressSnapshot extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'role_id',
        'user_id',
        'completed_tasks_count',
        'total_tasks_count',
        'completed_steps_count',
        'completed_phases_count',
        'snapshot_date',
    ];

    protected $casts = [
        'completed_tasks_count' => 'integer',
        'total_tasks_count' => 'integer',
        'completed_steps_count' => 'integer',
        'completed_phases_count' => 'integer',
        'snapshot_date' => 'date',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
