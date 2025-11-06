<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $rank
 * @property int $bNpcBase
 * @property int $mob_index
 * @property int $zone_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $names
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SpawnPoint> $spawn_points
 * @property-read int|null $spawn_points_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mob newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mob newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mob query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mob whereBNpcBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mob whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mob whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mob whereMobIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mob whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mob whereNames($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mob whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mob whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mob whereZoneId($value)
 * @mixin \Eloquent
 */
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
