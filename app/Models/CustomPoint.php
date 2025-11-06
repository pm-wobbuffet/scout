<?php

namespace App\Models;

use App\Enums\SpawnPointTypeEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $scout_id
 * @property int $zone_id
 * @property int|null $point_id
 * @property int|null $mob_id
 * @property string|null $line_source
 * @property string|null $x
 * @property string|null $y
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $point_type
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomPoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomPoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomPoint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomPoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomPoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomPoint whereLineSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomPoint whereMobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomPoint wherePointId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomPoint whereScoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomPoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomPoint whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomPoint whereY($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomPoint whereZoneId($value)
 * @mixin \Eloquent
 */
class CustomPoint extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $appends = ['point_type'];

    // Always return 'custom_spawn_point" as the point_type on these models
    // makes inserting into the ScoutPoints table easier, since
    // spawn points will have a similar entry
    protected function pointType(): Attribute
    {
        return Attribute::make(
            get: fn() => SpawnPointTypeEnum::CUSTOM_SPAWN_POINT,
        );
    }
}
