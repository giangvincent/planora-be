<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamificationEvent extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
