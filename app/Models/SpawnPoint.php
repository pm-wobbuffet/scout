<?php

namespace App\Models;

use App\Enums\SpawnPointTypeEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $zone_id
 * @property string $x
 * @property string $y
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read SpawnPointTypeEnum $point_type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mob> $valid_mobs
 * @property-read int|null $valid_mobs_count
 * @property-read \App\Models\Zone|null $zone
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint whereY($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint whereZoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpawnPoint withoutTrashed()
 * @mixin \Eloquent
 */
class SpawnPoint extends Model
{
    use SoftDeletes;

    public $table = 'spawn_points';

    protected $guarded = ['id'];
    protected $appends = ['point_type'];

    public function valid_mobs(): BelongsToMany
    {
        return $this->belongsToMany(Mob::class, 'mobs_spawn_points')
            ->withTimestamps();
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    // Always return 'spawn_point" as the point_type on these models
    // makes inserting into the ScoutSpawnPoint table easier, since
    // custom spawn points will have a similar entry
    protected function pointType(): Attribute
    {
        return Attribute::make(
            get: fn(): SpawnPointTypeEnum => SpawnPointTypeEnum::SPAWN_POINT,
        );
    }
}
