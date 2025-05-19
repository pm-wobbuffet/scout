<?php

namespace App\Http\Controllers;

use App\Events\ScoutAssignMobEvent;
use App\Http\Requests\Scout\AssignMobRequest;
use App\Models\Scout;
use App\Models\ScoutPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScoutPointController extends Controller
{
    //

    public function assignMob(AssignMobRequest $request, Scout $scout, string $password = '')
    {
        if(!$password || ($scout->collaborator_password !== $password)) {
            abort(403, 'Unauthorized action.');
        }
        // Delete any existing entries for this mob+instance
        ScoutPoint::where('scout_id', $scout->id)
        ->where('point_type', $request->validated('point_type'))
        ->where('point_id', $request->validated('point_id'))
        ->where('instance_number', $request->validated('instance_number'))
        ->delete();

        // Add new mob onto this point
        $point = $scout->points()->create([
            'point_type'        => $request->validated('point_type'),
            'point_id'          => $request->validated('point_id'),
            'zone_id'           => $request->validated('zone_id'),
            'instance_number'   => $request->validated('instance_number'),
            'mob_id'            => $request->validated('mob_id'),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        
        ScoutAssignMobEvent::dispatch($scout, $scout->points);
        return response()->json($scout->points);
    }

}
