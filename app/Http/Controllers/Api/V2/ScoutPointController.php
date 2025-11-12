<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\DeleteScoutPointRequest;
use App\Http\Resources\Api\V2\ScoutPointResource;
use App\Models\Scout;
use App\Models\ScoutPoint;
use App\Traits\BroadcastsScoutingEvents;
use Illuminate\Http\Request;

class ScoutPointController extends Controller
{
    use BroadcastsScoutingEvents;

    /**
     * Display all the Points mapped in this Scout Report
     */
    public function index(Scout $scout)
    {
        $scout->load(['points', 'points.mob']);
        return ScoutPointResource::collection($scout->points);
    }

    /**
     * Save a Point for this Scout Report
     */
    public function store(Request $request, Scout $scout)
    {
        //@todo build the request for this
    }

    /**
     * Remove a point from the ScoutPoint list
     */
    public function destroy(DeleteScoutPointRequest $request, Scout $scout, ScoutPoint $point)
    {
        $point->delete();
        // Returns the deleted point, just for reference
        return new ScoutPointResource($point);
    }
}
