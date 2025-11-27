<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCosmetic extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'cosmetic_id',
        'unlocked_at',
        'equipped',
    ];

    protected function casts(): array
    {
        return [
            'unlocked_at' => 'datetime',
            'equipped' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cosmetic()
    {
        return $this->belongsTo(Cosmetic::class);
    }
}
