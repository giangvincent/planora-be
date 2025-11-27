<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cosmetic extends Model
{
    protected $fillable = [
        'key',
        'category',
        'name',
        'rarity',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function userCosmetics()
    {
        return $this->hasMany(UserCosmetic::class);
    }
}
