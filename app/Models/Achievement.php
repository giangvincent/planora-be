<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'icon',
    ];

    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class);
    }
}
