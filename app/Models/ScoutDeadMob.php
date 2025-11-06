<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $scout_id
 * @property int $mob_id
 * @property int $instance_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutDeadMob newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutDeadMob newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutDeadMob query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutDeadMob whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutDeadMob whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutDeadMob whereInstanceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutDeadMob whereMobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutDeadMob whereScoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutDeadMob whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ScoutDeadMob extends Model
{
    
    protected $guarded = ['id'];
}
