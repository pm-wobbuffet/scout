<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $appends = ['total_mobs'];

    /* Accessors and Mutators */

    public function totalMobs(): Attribute
    {
        return Attribute::make(
            get: function(mixed $value) {
                if($this->relationLoaded('mobs')) {
                    return $this->default_instances * $this->mobs()->count();
                }
            }
        );
    }

    /* Relations */

    public function mobs(): HasMany
    {
        return $this->hasMany(Mob::class);
    }

    /* Private Methods */
}
