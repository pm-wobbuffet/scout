<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mob extends Model
{
    protected $hidden = ['updated_at', 'created_at'];

    protected function casts(): array
    {
        return [
            'names' =>  'array',
        ];
    }

    public function spawn_points()
    {
        return $this->belongsToMany(SpawnPoint::class, 'mobs_spawn_points');
    }
}
