<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpawnPoint extends Model
{
    use SoftDeletes;
    
    public $table = 'spawn_points';

    public function valid_mobs(): BelongsToMany
    {
        return $this->belongsToMany(Mob::class, 'mobs_spawn_points')
        ->withTimestamps();
    }
}
