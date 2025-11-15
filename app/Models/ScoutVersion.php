<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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
            $d = DB::table('scout_versions')
                ->where('scout_id', $scoutVersion->scout_id)
                ->where('created_at', '>=', Carbon::now()->subSeconds(30))
                ->orderBy('created_at', 'desc')
                ->first();
            if ($d) {
                return false;
            }

            $s = DB::table('scout_versions')
                ->selectRaw('IFNULL(MAX(version), 0)+1 as new_ver')
                ->where('scout_id', $scoutVersion->scout_id)
                ->first();
            $scoutVersion->version = $s->new_ver;
        });
    }
}
