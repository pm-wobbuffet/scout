<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\ScoutCustomPoint;
use App\Models\SpawnPoint;

/**
 * Represents the status of a Point within a given scout request, including occupancy status.
 *
 * @property int $id
 * @property int $scout_id
 * @property int $zone_id
 * @property string $point_type
 * @property int $point_id
 * @property int $instance_number
 * @property int|null $mob_id
 * @property string|null $x
 * @property string|null $y
 * @property string|null $reporter
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $point
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint whereInstanceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint whereMobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint wherePointId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint wherePointType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint whereReporter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint whereScoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint whereY($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutPoint whereZoneId($value)
 * @mixin \Eloquent
 */
class ScoutPoint extends Model
{
    protected $table = 'scout_points';
    protected $guarded = ['id'];

    protected $hidden = ['scout_id'];

    protected $with = ['point'];

    /* Relations */

    /**
     * The underlying SpawnPoint or CustomSpawnPoint
     * @return ScoutCustomPoint|SpawnPoint
     */
    public function point(): MorphTo
    {
        return $this->morphTo();
    }

    public function mob(): BelongsTo
    {
        return $this->belongsTo(Mob::class);
    }

    /* Private methods */

    /* Event methods */

    protected static function booted()
    {
        // Check for custom points on saving the model and remap to their newly created ID
        // since on the client side, the IDs are generated in the negative to prevent ID clashing
        static::saving(function (ScoutPoint $point) {
            if ($point->point_id < 0) {
                // This was a custom point that needs to be remapped
                $custom_point = ScoutCustomPoint::query()
                    ->where('scout_id', $point->scout_id)
                    ->where('internal_id', $point->point_id)
                    ->firstOrFail();
                $point->point_id = $custom_point->id;
                $point->point_type = 'custom_spawn_point';
            }
        });
    }
}
