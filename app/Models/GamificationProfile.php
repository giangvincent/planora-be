<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamificationProfile extends Model
{
    protected $fillable = [
        'user_id',
        'level',
        'xp',
        'coins',
        'current_streak',
        'longest_streak',
        'last_active_date',
    ];

    protected function casts(): array
    {
        return [
            'last_active_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
