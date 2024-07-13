<?php

namespace App\Traits\Traits;

use App\Http\Requests\StoreScoutRequest;
use App\Http\Requests\UpdateScoutAPIRequest;
use App\Http\Requests\UpdateScoutRequest;
use App\Models\CustomPoint;
use App\Models\Scout;
use App\Models\Zone;

trait HandlesCustomPoints
{
    /**
     * Delete any saved custom points that were assigned to this ID
     * Used when a custom point is cleared of any mob
     * @param \App\Models\Scout $scout
     * @param int $point_id
     * @return void
     */
    private function deleteCustomPointsForScout(Scout $scout, int $point_id): void
    {
        CustomPoint::query()
        ->where('scout_id', $scout->id)
        ->where('point_id', $point_id)
        ->delete();
    }
}
