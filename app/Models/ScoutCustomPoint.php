<?php

namespace App\Models;

use App\Enums\SpawnPointTypeEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $scout_id
 * @property int $zone_id
 * @property string $x
 * @property string $y
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $internal_id
 * @property-read SpawnPointTypeEnum $point_type
 * @property-read \App\Models\Scout $scout
 * @property-read \App\Models\Zone $zone
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutCustomPoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutCustomPoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutCustomPoint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutCustomPoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutCustomPoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutCustomPoint whereInternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutCustomPoint whereScoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutCustomPoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutCustomPoint whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutCustomPoint whereY($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutCustomPoint whereZoneId($value)
 * @mixin \Eloquent
 */
class ScoutCustomPoint extends Model
{
    protected $fillable = [
        'id',
        'scout_id',
        'zone_id',
        'x',
        'y',
        'created_at',
        'updated_at',
        'internal_id',
        'assigned_by_import'
    ];
    protected $hidden = ['scout_id'];
    protected $appends = ['point_type'];

    public $table = 'scout_custom_points';

    public function scout()
    {
        return $this->belongsTo(Scout::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    // Always return 'custom_spawn_point" as the point_type on these models
    // makes inserting into the ScoutPoints table easier, since
    // spawn points will have a similar entry
    protected function pointType(): Attribute
    {
        return Attribute::make(
            get: fn(): SpawnPointTypeEnum => SpawnPointTypeEnum::CUSTOM_SPAWN_POINT,
        );
    }
}
