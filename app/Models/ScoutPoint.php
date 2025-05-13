<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ScoutPoint extends Model
{
    

    /* Relations */

    public function point(): MorphTo
    {
        return $this->morphTo();
    }

    /* Private methods */
}
