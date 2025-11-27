<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class World extends Model
{
    protected $fillable = [
        'user_id',
        'level',
        'theme',
        'weather',
        'state',
    ];

    protected function casts(): array
    {
        return [
            'state' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tiles()
    {
        return $this->hasMany(WorldTile::class);
    }

    public function objects()
    {
        return $this->hasMany(WorldObject::class);
    }
}
