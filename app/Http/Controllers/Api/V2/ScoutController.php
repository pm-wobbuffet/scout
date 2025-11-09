<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\StoreScoutRequest;
use App\Http\Requests\Api\V2\UpdateScoutRequest;
use App\Http\Resources\Api\V2\ScoutResource;
use App\Models\Scout;
use App\Traits\BroadcastsScoutingEvents;
use App\Traits\UpdatesScoutReports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoutController extends Controller
{
    use UpdatesScoutReports, BroadcastsScoutingEvents;

    /**
     * Store a new Scout report
     */
    public function store(StoreScoutRequest $request)
    {
        $scout = Scout::create($request->validated());
        $request->merge([
            'collaborator_password' => $scout->collaborator_password,
        ]);
        if ($request->has('custom_points')) {
            $custom_points_mapping = $this->handleCustomPoints($scout, $request->validated('custom_points'));
        }
        if ($request->has('points')) {
            $scout->points()->createMany($request->validated('points'));
        }
        if ($request->has('instance_data')) {
            $scout->instances()->sync($request->validated('instance_data'));
        }
        if ($request->has('dead_mobs')) {
            $scout->dead_mobs()->createMany($request->validated('dead_mobs'));
        }
        if ($request->has('scouts') && $request->validated('scouts') !== null) {
            $scout->scouts()->createMany($request->validated('scouts'));
        }

        return new ScoutResource($scout);
    }

    /**
     * Get info for a single Scout report
     */
    public function show(Scout $scout, Request $request)
    {
        $scout->load(['points', 'points.point', 'custom_points', 'scouts', 'dead_mobs']);
        if (!$request->has('collaborator_password')) {
            $scout->makeHidden('collaborator_password');
        }
        return new ScoutResource($scout);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScoutRequest $request, Scout $scout)
    {
        $this->addScouterToScoutReport($scout, $request->validated('update_user'));
        if ($request->has('dead_mobs')) {
            // Remove any mobs marked as "living" from the database of dead mobs
            $coll = collect($request->validated('dead_mobs'));
            $living_mobs = $coll->where('is_dead', '=', 0)->map(function ($value) {
                return "{$value['mob_id']}-{$value['instance_number']}";
            });
            if (count($living_mobs) > 0) {
                $scout->dead_mobs()->whereIn(
                    DB::raw('CONCAT(mob_id,"-",instance_number)'),
                    $living_mobs
                )->delete();
            }
            $scout->dead_mobs()->upsert(
                $coll->where('is_dead', '=', 1)
                    ->select(['mob_id', 'instance_number'])->toArray(),
                ['mob_id', 'instance_number']
            );
            $this->ScoutMobsStatusUpdated($scout);
        }
        if ($request->has('sightings')) {
            //dd($request->validated('sightings'));
            $scout->points()->createMany($request->validated('sightings'));
        }
        $scout->update($request->validated());
        $scout->save();
        $scout->load(['points', 'scouts', 'custom_points', 'dead_mobs']);
        return new ScoutResource($scout);
    }
}
