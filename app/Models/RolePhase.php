<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RolePhase extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'role_id',
        'title',
        'description',
        'order',
        'estimated_duration_weeks',
    ];

    protected $casts = [
        'estimated_duration_weeks' => 'integer',
        'order' => 'integer',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(PhaseStep::class, 'phase_id');
    }
}
