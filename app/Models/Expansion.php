<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expansion extends Model
{
    /* Accessors and Mutators */

    /* Relations */

    // public function mobs(): HasMany
    // {
    //     return $this->hasMany(Mob::class);
    // }

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }
}
