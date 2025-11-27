<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldObject extends Model
{
    protected $fillable = [
        'world_id',
        'type',
        'x',
        'y',
        'sprite_key',
        'state',
    ];

    protected function casts(): array
    {
        return [
            'state' => 'array',
        ];
    }

    public function world()
    {
        return $this->belongsTo(World::class);
    }
}
