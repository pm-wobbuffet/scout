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

    protected static function booted()
    {
        static::creating(function (ScoutVersion $scoutVersion) {
            // Check to see if there's a version created in the previous 30 sec
            // $d = DB::table('scout_versions')
            //     ->where('scout_id', $scoutVersion->scout_id)
            //     ->where(
            //         'created_at',
            //         '>=',
            //         Carbon::now()->subSeconds(env('VERSION_HISTORY_LOCKOUT_SEC', 30))
            //     )
            //     ->orderBy('created_at', 'desc')
            //     ->first();
            // if ($d) {
            //     return false;
            // }

            $s = DB::table('scout_versions')
                ->selectRaw('IFNULL(MAX(version), 0)+1 as new_ver')
                ->where('scout_id', $scoutVersion->scout_id)
                ->first();
            $scoutVersion->version = $s->new_ver;
        });
    }
}
