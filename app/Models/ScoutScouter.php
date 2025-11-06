<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $scout_id
 * @property string $scout_name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutScouter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutScouter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutScouter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutScouter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutScouter whereScoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutScouter whereScoutName($value)
 * @mixin \Eloquent
 */
class ScoutScouter extends Model
{
    //
    protected $table = 'scout_scouters';
    protected $guarded = ['id'];

    public $timestamps = false;
}
