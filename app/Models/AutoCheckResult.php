<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class AutoCheckResult extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'auto_check_id',
        'user_id',
        'score',
        'max_score',
        'passed',
        'attempt_data',
    ];

    protected $casts = [
        'attempt_data' => 'array',
        'score' => 'integer',
        'max_score' => 'integer',
        'passed' => 'boolean',
    ];

    public function autoCheck(): BelongsTo
    {
        return $this->belongsTo(AutoCheck::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
