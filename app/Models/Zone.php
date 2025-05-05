<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $appends = ['total_mobs'];

    protected $hidden = ['updated_at', 'created_at'];

    protected function casts(): array
    {
        return [
            'names' =>  'array',
        ];
    }

    /* Accessors and Mutators */

    public function totalMobs(): Attribute
    {
        return Attribute::make(
            get: function(mixed $value) {
                if($this->relationLoaded('mobs')) {
                    return $this->default_instances * $this->mobs->count();
                }
            }
        );
    }

    /* Relations */

    public function mobs(): HasMany
    {
        return $this->hasMany(Mob::class);
    }

    public function aetherytes(): HasMany
    {
        return $this->hasMany(Aetheryte::class);
    }

    public function spawn_points(): HasMany
    {
        return $this->hasMany(SpawnPoint::class, 'zone_id', 'id');
    }

    public function expansion(): BelongsTo
    {
        return $this->belongsTo(Expansion::class);
    }

    /* Private Methods */
}
