<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $scout_id
 * @property string|null $user
 * @property \Illuminate\Support\Collection $scout_details
 * @property \Illuminate\Database\Eloquent\Casts\ArrayObject<array-key, mixed>|null $update_details
 * @property int|null $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Scout|null $scout
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutVersion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutVersion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutVersion whereScoutDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutVersion whereScoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutVersion whereUpdateDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutVersion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutVersion whereUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScoutVersion whereVersion($value)
 * @mixin \Eloquent
 */
class ScoutVersion extends Model
{
    protected $guarded = ['id'];

    /* Accessors/Mutators/Casts */

    /**
     * Override casts
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scout_details'     => AsCollection::class,
            'update_details'    => AsArrayObject::class,
        ];
    }

    public function scout(): BelongsTo
    {
        return $this->belongsTo(Scout::class);
    }
}
