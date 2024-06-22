<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SpawnPoint extends Model
{
    public $table = 'spawn_points';

    public function valid_mobs(): BelongsToMany
    {
        return $this->belongsToMany(Mob::class, 'mobs_spawn_points');
    }
}
