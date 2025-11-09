<?php

namespace App\Traits;

use App\Events\Scout\UpdateAllMobStatus;
use App\Models\Scout;

trait BroadcastsScoutingEvents
{
    /**
     * Send a client event indicating that all mob statuses should
     * be equal to the supplied array
     * @param \App\Models\Scout $scout
     * @return void
     */
    public function ScoutMobsStatusUpdated(Scout $scout)
    {
        broadcast(new UpdateAllMobStatus($scout))->toOthers();
    }
}
