<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $scout_id
 * @property string $x
 * @property string $y
 * @property string $mob_index
 * @property int $zone_id
 * @property int $instance_number
 * @property int|null $point_id
 * @property array<array-key, mixed>|null $previous_instance_data
 * @property array<array-key, mixed>|null $previous_point_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $previous_custom_points
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate whereInstanceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate whereMobIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate wherePointId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate wherePreviousCustomPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate wherePreviousInstanceData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate wherePreviousPointData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate whereScoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate whereY($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutUpdate whereZoneId($value)
 * @mixin \Eloquent
 */
class ScoutUpdate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'previous_instance_data' =>  'array',
            'previous_point_data'    =>  'array',
            'previous_custom_points' =>  'array',
        ];
    }
}
