<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IntegrationProvider;
use App\Enums\IntegrationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Integration extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'provider',
        'access_token',
        'refresh_token',
        'expires_at',
        'settings',
        'status',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'expires_at' => 'datetime',
        'settings' => 'array',
        'provider' => IntegrationProvider::class,
        'status' => IntegrationStatus::class,
    ];

    protected $attributes = [
        'status' => IntegrationStatus::Connected->value,
        'settings' => '{}',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeProvider(Builder $query, IntegrationProvider|string|null $provider): Builder
    {
        if ($provider instanceof IntegrationProvider) {
            $provider = $provider->value;
        }

        return $provider === null ? $query : $query->where('provider', $provider);
    }

    public function scopeStatus(Builder $query, IntegrationStatus|string|null $status): Builder
    {
        if ($status instanceof IntegrationStatus) {
            $status = $status->value;
        }

        return $status === null ? $query : $query->where('status', $status);
    }
}
