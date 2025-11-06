<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $zone_id
 * @property string $x
 * @property string $y
 * @property string $name
 * @property int $icon
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $names
 * @property-read mixed $x_norm
 * @property-read mixed $y_norm
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aetheryte newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aetheryte newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aetheryte query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aetheryte whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aetheryte whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aetheryte whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aetheryte whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aetheryte whereNames($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aetheryte whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aetheryte whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aetheryte whereY($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aetheryte whereZoneId($value)
 * @mixin \Eloquent
 */
class Aetheryte extends Model
{
    //protected   $appends = ['x_norm', 'y_norm'];

    protected function casts(): array
    {
        return [
            'names' =>  'array',
        ];
    }
    
    public function xNorm(): Attribute
    {
        return Attribute::make(
            get: function() {
                return round( ($this->x / 42) * 100, 1) . '%';
            }
        );
    }

    public function yNorm(): Attribute
    {
        return Attribute::make(
            get: function() {
                return round( ($this->y / 42) * 100, 1) . '%';
            }
        );
    }
}
