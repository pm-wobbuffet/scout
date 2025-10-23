<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ScoutPoint extends Model
{
    protected $table = 'scout_points';
    protected $guarded = ['id'];

    /* Relations */

    public function point(): MorphTo
    {
        return $this->morphTo();
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
