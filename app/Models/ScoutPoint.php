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
}
