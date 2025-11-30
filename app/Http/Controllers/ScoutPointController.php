<?php

namespace App\Http\Controllers;

use App\Events\ScoutAssignMob;
use App\Events\ScoutClearPoint;
use App\Events\ScoutUpdateMobStatus;
use App\Http\Requests\Scout\AssignMobRequest;
use App\Http\Requests\Scout\ClearPointRequest;
use App\Http\Requests\Scout\UpdateMobStatusRequest;
use App\Http\Resources\ScoutCustomPointResource;
use App\Models\Mob;
use App\Models\Scout;
use App\Models\ScoutDeadMob;
use App\Models\ScoutPoint;
use App\Models\Zone;
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

        $this->sendScoutMobAssignedEvent($scout, $request->validated('zone_id'), $request->validated('instance_number', 1));
        $this->sendReportModifiedEvent($scout, [
            'name'              => 'Mob Assigned',
            'zone_id'           => $request->validated('zone_id'),
            'mob_id'            => $request->validated('mob_id', null),
            'point_id'          => $request->validated('point_id', null),
            'point_type'        => $request->validated('point_type', null),
            'instance_number'   => $request->validated('instance_number', null)
        ]);
        return response()->json(['custom_points' => collect(ScoutCustomPointResource::collection($scout->custom_points))->toArray()]);
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

        $this->sendPointClearedEvent($scout, $request->validated('point_type'), $request->validated('id'), $request->validated('instance_number', 1));
        $this->sendReportModifiedEvent($scout, [
            'name' => 'Point Cleared',
            'zone_id' => $request->validated('zone_id'),
            'instance_number' => $request->validated('instance_number'),
            'point_id' => $request->validated('id'),
            'point_type' => $request->validated('point_type'),
        ]);
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

        $this->sendReportModifiedEvent($scout, [
            'name'              => 'Mob Status Updated',
            'is_dead'           => $request->validated('is_dead'),
            'instance_number'   => $request->validated('instance_number'),
            'mob_id'            => $request->validated('mob_id'),
            'zone_id'           => Mob::whereId($request->validated('mob_id'))->first()->zone_id,
        ]);

        return response()->json(['success' => true]);
    }
}
