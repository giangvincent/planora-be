<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RoleSourceType;
use App\Enums\RoleStatus;
use App\Enums\RoleVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Role extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'source_type',
        'source_meta',
        'visibility',
        'status',
        'estimated_duration_weeks',
    ];

    protected $casts = [
        'source_meta' => 'array',
        'source_type' => RoleSourceType::class,
        'visibility' => RoleVisibility::class,
        'status' => RoleStatus::class,
        'estimated_duration_weeks' => 'integer',
    ];

    protected $attributes = [
        'source_type' => RoleSourceType::Manual,
        'visibility' => RoleVisibility::Private,
        'status' => RoleStatus::Draft,
    ];

    protected static function booted(): void
    {
        static::creating(function (Role $role): void {
            if (empty($role->slug)) {
                $role->slug = $role->generateUniqueSlug();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(RolePhase::class);
    }

    public function progressSnapshots(): HasMany
    {
        return $this->hasMany(RoleProgressSnapshot::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    private function generateUniqueSlug(): string
    {
        $baseSlug = Str::slug($this->title) ?: Str::random(6);
        $slug = $baseSlug;
        $userId = $this->user_id ?? 0;
        $counter = 1;

        while (self::where('user_id', $userId)->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
