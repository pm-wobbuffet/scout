<?php

namespace App\Http\Controllers;

use App\Events\Scout\InstanceCountsUpdated;
use App\Http\Requests\Scout\UpdateInstanceCountRequest;
use App\Models\Scout;
use App\Traits\UpdatesScoutReports;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class ScoutController extends Controller
{
    use UpdatesScoutReports;

    public function updateInstances(UpdateInstanceCountRequest $request, Scout $scout, string $password)
    {
        $this->authorizeUpdate($scout, $password);
        // Make sure they didn't somehow submit an invalid instance count by manual submission
        // Should always be a positive number >= 1. No maximum on it for now, just in case Square ever picks
        // a higher number (they surprised us with 6 instead of 3 for DT on release)
        $counts = [];
        foreach ($request->validated('instance_data') as $zone_id => $instance_count) {
            $instance_count = ($instance_count < 1) ? 1 : $instance_count;
            $counts[] = ['zone_id' => $zone_id, 'instance_count' => $instance_count];
        }
        $scout->instances()->sync($counts);
        // TODO: consider deleting any points who are now no longer in a valid instance
        // Considerations: if they change a number lower on accident, we may not want to wipe the data, so they
        // can get it back.
        /*
        \DB::table('scout_points')
            ->join('scout_zone_instances', function (JoinClause $join) {
                $join->on('scout_points.scout_id', '=', 'scout_zone_instances.scout_id')
                    ->on('scout_points.zone_id', '=', 'scout_zone_instances.zone_id');
            })
            ->whereRaw('scout_points.instance_number > scout_zone_instances.instance_count')
            ->delete();
        */
        broadcast(new InstanceCountsUpdated($scout))->toOthers();
        return response()->json($scout->instances);
    }
}
