<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\Scout\PointOccupancyChanged;
use App\Events\Scout\ZoneMultipleOccupancyChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreScoutRequest;
use App\Http\Requests\Api\V1\BulkUpdateScoutApiRequest;
use App\Http\Requests\Api\V1\UpdateOccupiedPointRequest;
use App\Http\Resources\ScoutCustomPointResource;
use App\Models\Scout;
use App\Traits\UpdatesScoutReports;
use Illuminate\Support\Facades\DB;


class ScoutController extends Controller
{
    #use HandlesScoutUpdates, HandlesCustomPoints;
    use UpdatesScoutReports;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScoutRequest $request)
    {
        $s = Scout::create($request->safe()->all());
        if ($request->has('point_data')) {
            $s->points()->createMany($request->point_data);
        }
        if ($request->validated('instance_data')) {
            foreach ($request->validated('instance_data') as $zone_id => $instance_count) {
                $s->instances()->attach($zone_id, ['instance_count' => $instance_count]);
            }
        }
        if ($request->validated('scouts')) {
            $s->scouts()->createMany($request->validated('scouts'));
        }
        if ($request->validated('mob_status') && is_array($request->validated('mob_status'))) {
            // Convert any old mob statuses to the new format
            $mobs = [];
            if ($request->validated('mob_status') && is_array($request->validated('mob_status'))) {
                foreach ($request->validated('mob_status') as $mob_id => $instances) {
                    foreach ($instances as $instance_number => $status) {
                        if ($status) {
                            $mobs[] = [
                                'mob_id' => $mob_id,
                                'instance_number' => $instance_number,
                            ];
                        }
                    }
                }
            }
            $s->dead_mobs()->createMany($mobs);
        }
        if ($s) {
            return response()->json([
                'slug'                  => $s->slug,
                'collaborator_password' => $s->collaborator_password,
                'readonly_url'          => route('scout.view', $s),
                'collaborate_url'       => route('scout.view', [$s, $s->collaborator_password]),
            ]);
        } else {
            return response()->json([
                'error' => 'An unknown error was created while trying to store this scouting report.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BulkUpdateScoutApiRequest $request, Scout $scout)
    {
        $modified_zones = $this->createBulkUpdate($scout, $request->validated('sightings'));
        // Make sure to credit the user if a username was supplied
        if ($request->has('update_user') && $request->input('update_user') !== 'Anonymous') {
            if (!in_array($request->input('update_user'), $scout->scouts)) {
                $scout->scouts = [...$scout->scouts, $request->input('update_user')];
            }
        }
        $scout->save();

        $points = $scout->points()->whereIn(
            DB::raw("CONCAT(zone_id,'-',instance_number)"),
            array_keys($modified_zones)
        )->get();
        broadcast(new ZoneMultipleOccupancyChanged(
            $scout,
            $points,
            collect(ScoutCustomPointResource::collection($scout->custom_points))->toArray(),
            array_keys($modified_zones),
        ));

        return response()->json([
            'scout_id'              =>  $scout->slug,
            'collaborator_password' =>  $scout->collaborator_password,
            'readonly_url'          =>  route('scout.view', $scout),
            'collaborate_url'       =>  route('scout.view', [$scout, $scout->collaborator_password]),
            'processed_sightings'   =>  $request->validated('sightings'),
        ]);
    }

    public function bulkUpdate(BulkUpdateScoutApiRequest $request, Scout $scout)
    {
        $this->createBulkUpdate($scout, $request->validated('sightings'));
        return response()->json([
            'scout_id'              =>  $scout->slug,
            'collaborator_password' =>  $scout->collaborator_password,
            'readonly_url'          =>  route('scout.view', $scout),
            'collaborate_url'       =>  route('scout.view', [$scout, $scout->collaborator_password]),
            'processed_sightings'   =>  $request->validated('sightings'),
        ]);
    }

    public function updateOccupiedPoint(UpdateOccupiedPointRequest $request, Scout $scout)
    {
        // Get details from the request
        $point_id = $request->validated('point_id');
        $instance = $request->validated('instance_number');
        $status = $request->validated('status');
        $distance = $request->distance;
        if ($distance && floatval($distance) > 2) {
            return response()->json([
                'error'             =>  'The specified point was not within range of a known A rank spawn point.',
                'distance'          =>  $distance,
                'closest_point'     =>  $point_id,
            ], 422); // 422 = Unprocessable Input
        }
        $p = $scout->points()->where('point_type', $request->point->point_type)
            ->where('point_id', $request->point->id)->first();
        if ($p && $p->mob_id !== null) {
            // There was a mark on this spot, don't let them do any occupied update
            return response()->json([
                'error' => 'An A Rank Mark was already placed on this point. Cannot mark as occupied. Remove the A rank first if needed.',
            ], 422); // 422 = Unprocessable input
        }
        if ($p) $p->delete();
        if ($status == 1) {
            // Mark as occupied
            $scout->points()->create([
                'point_type' => $request->point->point_type,
                'point_id'  => $point_id,
                'zone_id'   => $request->point->zone_id,
                'instance_number' => $instance,
                'mob_id'    => null,
                'reporter'  => $request->input('update_user', null),
            ]);
        }
        // Make sure to credit the user if a username was supplied
        if ($request->has('update_user') && $request->input('update_user') !== 'Anonymous') {
            $this->addScouterToScoutReport($scout, $request->input('update_user'));
        }
        $scout->save();

        $points = $scout->points
            ->where('zone_id', $request->validated('zone_id'))
            ->where('instance_number', $request->validated('instance_number', 1));
        broadcast(new PointOccupancyChanged(
            $scout,
            $points,
            $request->validated('zone_id'),
            $request->validated('instance_number', 1)
        ))
            ->toOthers();
        return [
            'success'           =>  1,
            'occupied_points'   => $scout->points,
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
