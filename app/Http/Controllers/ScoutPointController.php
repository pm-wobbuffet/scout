<?php

namespace App\Http\Controllers;

use App\Events\ScoutAssignMob;
use App\Events\ScoutClearPoint;
use App\Events\ScoutUpdateMobStatus;
use App\Http\Requests\Scout\AssignMobRequest;
use App\Http\Requests\Scout\ClearPointRequest;
use App\Http\Requests\Scout\UpdateMobStatusRequest;
use App\Http\Resources\ScoutCustomPointResource;
use App\Models\Scout;
use App\Models\ScoutDeadMob;
use App\Models\ScoutPoint;
use App\Traits\UpdatesScoutReports;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoutPointController extends Controller
{
    use UpdatesScoutReports;

    public function assignMob(AssignMobRequest $request, Scout $scout, string $password = '')
    {
        $this->authorizeUpdate($scout, $password);

        // Delete any existing entries for this mob+instance
        ScoutPoint::where('scout_id', $scout->id)
            ->where('point_type', $request->validated('point_type'))
            ->where('point_id', $request->validated('point_id'))
            ->where('instance_number', $request->validated('instance_number'))
            ->delete();
        ScoutPoint::where('scout_id', $scout->id)
            ->where('mob_id', $request->validated('mob_id'))
            ->where('instance_number', $request->validated('instance_number'))
            ->delete();


        // Add new mob onto this point
        $point = $scout->points()->create([
            'point_type'        => $request->validated('point_type'),
            'point_id'          => $request->validated('point_id'),
            'zone_id'           => $request->validated('zone_id'),
            'instance_number'   => $request->validated('instance_number'),
            'mob_id'            => $request->validated('mob_id'),
            'reporter'          => $request->validated('reporter'),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        // Handle meta update
        if ($request->has('reporter')) {
            $this->addScouterToScoutReport($scout, $request->validated('reporter'));
        }

        broadcast(
            new ScoutAssignMob(
                $scout,
                $request->validated('zone_id'),
                $request->validated('instance_number', 1),
                $scout->points->where('zone_id', $request->validated('zone_id'))
                    ->where('instance_number', $request->validated('instance_number'))->values()
            )
        )->toOthers();
        return response()->json(['success' => true, 'custom_points' => collect(ScoutCustomPointResource::collection($scout->custom_points))->toArray()]);
    }

    public function clearPoint(ClearPointRequest $request, Scout $scout, string $password)
    {
        $this->authorizeUpdate($scout, $password);

        ScoutPoint::query()
            ->where('scout_id', $scout->id)
            ->where('point_type', $request->validated('point_type'))
            ->where('point_id', $request->validated('id'))
            ->where('instance_number', $request->validated('instance_number', 1))
            ->delete();

        broadcast(
            new ScoutClearPoint(
                $scout,
                $request->validated('id'),
                $request->validated('point_type'),
                $request->validated('instance_number', 1)
            )
        )->toOthers();
        return response()->json(['success' => true]);
    }

    public function updateMobStatus(UpdateMobStatusRequest $request, Scout $scout, string $password): JsonResponse
    {
        $this->authorizeUpdate($scout, $password);
        // Delete any existing dead mobs matching this
        ScoutDeadMob::query()
            ->where('scout_id', $scout->id)
            ->where('mob_id', $request->validated('mob_id'))
            ->where('instance_number', $request->validated('instance_number'))
            ->delete();

        // TODO: Check and make sure the mob isn't already assigned to the map

        if ($request->validated('is_dead')) {
            $scout->dead_mobs()->create([
                'mob_id'            => $request->validated('mob_id'),
                'instance_number'   => $request->validated('instance_number'),
            ]);
        }
        broadcast(new ScoutUpdateMobStatus(
            $scout,
            $request->validated('mob_id'),
            $request->validated('instance_number'),
            $request->validated('is_dead')
        ))->toOthers();

        return response()->json(['success' => true]);
    }
}
