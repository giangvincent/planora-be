<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationStatus;
use App\Enums\NotificationTransport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Notification extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'task_id',
        'channel',
        'scheduled_for',
        'sent_at',
        'payload',
        'status',
        'error',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
        'payload' => 'array',
        'status' => NotificationStatus::class,
        'channel' => NotificationTransport::class,
    ];

    protected $attributes = [
        'status' => NotificationStatus::Pending->value,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function scopeStatus(Builder $query, NotificationStatus|string|null $status): Builder
    {
        if ($status instanceof NotificationStatus) {
            $status = $status->value;
        }

        return $status === null ? $query : $query->where('status', $status);
    }

    public function scopeScheduledBetween(Builder $query, ?string $start, ?string $end): Builder
    {
        if ($start !== null) {
            $query->where('scheduled_for', '>=', $start);
        }

        if ($end !== null) {
            $query->where('scheduled_for', '<=', $end);
        }

        return $query;
    }
}
