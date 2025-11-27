<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldTile extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'world_id',
        'x',
        'y',
        'terrain',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function world()
    {
        return $this->belongsTo(World::class);
    }
}
