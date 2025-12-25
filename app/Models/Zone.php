<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int $default_instances
 * @property int $map_id
 * @property int $expansion_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $spoiler_until
 * @property int $size_factor
 * @property string $max_coord_size
 * @property int $allow_custom_points
 * @property int $sort_priority
 * @property array<array-key, mixed>|null $names
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Aetheryte> $aetherytes
 * @property-read int|null $aetherytes_count
 * @property-read \App\Models\Expansion|null $expansion
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mob> $mobs
 * @property-read int|null $mobs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SpawnPoint> $spawn_points
 * @property-read int|null $spawn_points_count
 * @property-read mixed $total_mobs
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone whereAllowCustomPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone whereDefaultInstances($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone whereExpansionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone whereMapId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone whereMaxCoordSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone whereNames($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone whereSizeFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone whereSortPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Zone extends Model
{
    protected $appends = ['total_mobs'];

    protected $hidden = ['updated_at', 'created_at'];

    protected function casts(): array
    {
        return [
            'names'         => 'array',
            'spoiler_until' => 'datetime',
        ];
    }

    /* Accessors and Mutators */

    public function totalMobs(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value) {
                if ($this->relationLoaded('mobs')) {
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
