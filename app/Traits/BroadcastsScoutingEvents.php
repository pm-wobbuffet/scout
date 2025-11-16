<?php

namespace App\Traits;

use App\Events\Scout\PointOccupancyChanged;
use App\Events\Scout\UpdateAllMobStatus;
use App\Events\Scout\ZoneMultipleOccupancyChanged;
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

    /**
     * Send a client event indicating that multiple zones had points updated in a single request
     * @param \App\Models\Scout $scout
     * @param string[] $zone_list - list of areas updated in format {zone_id}-{instance_number}
     * @return void
     */
    public function ScoutMultipleOccupanyUpdates(Scout $scout, array $zone_list)
    {
        broadcast(new ZoneMultipleOccupancyChanged($scout, $zone_list));
    }

    public function PointOccupacyUpdateEvent(Scout $scout, $points, $zone_id, $instance_number)
    {
        broadcast(new PointOccupancyChanged(
            $scout,
            $points,
            $zone_id,
            $instance_number
        ))->toOthers();
    }
}
