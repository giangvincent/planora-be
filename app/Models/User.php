<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationChannel;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * Default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'settings' => '{}',
        'notification_channel' => NotificationChannel::Both->value,
        'timezone' => 'Asia/Ho_Chi_Minh',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
        'notification_channel',
        'settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
            'notification_channel' => NotificationChannel::class,
            'last_login_at' => 'datetime',
        ];
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function calendarEntries(): HasMany
    {
        return $this->hasMany(CalendarEntry::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(Integration::class);
    }

    public function gamificationProfile()
    {
        return $this->hasOne(GamificationProfile::class);
    }

    public function world()
    {
        return $this->hasOne(World::class);
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('unlocked_at', 'meta')
            ->using(UserAchievement::class);
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function canAccessPanel(
        \Filament\Panel $panel
    ): bool {
        return $this->hasAnyRole(['admin', 'support']);
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function routeNotificationForWebPush(): ?array
    {
        $subscription = $this->settings['webpush']['subscription'] ?? null;

        return is_array($subscription) ? $subscription : null;
    }

    public function preferredNotificationChannels(): array
    {
        $channel = $this->notification_channel instanceof \BackedEnum
            ? $this->notification_channel->value
            : $this->notification_channel;

        return match ($channel) {
            'push' => ['push'],
            'email' => ['email'],
            default => ['email', 'push'],
        };
    }
}
