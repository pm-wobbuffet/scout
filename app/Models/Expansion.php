<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property string $abbreviation
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mob> $mobs
 * @property-read int|null $mobs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Zone> $zones
 * @property-read int|null $zones_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expansion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expansion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expansion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expansion whereAbbreviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expansion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expansion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expansion whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expansion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
