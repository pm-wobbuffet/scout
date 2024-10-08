<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Expansion extends Model
{
    protected $hidden = ['created_at', 'updated_at'];

    /* Accessors and Mutators */

    /* Relations */

    public function mobs(): HasManyThrough
    {
        return $this->hasManyThrough(Mob::class, Zone::class);
    }

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class)
        ->orderBy('sort_priority');
    }
}
