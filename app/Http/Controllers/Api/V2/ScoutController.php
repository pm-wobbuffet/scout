<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\StoreScoutRequest;
use App\Http\Requests\Api\V2\UpdateScoutRequest;
use App\Http\Resources\Api\V2\ScoutResource;
use App\Models\Scout;
use App\Traits\UpdatesScoutReports;
use Illuminate\Http\Request;

class ScoutController extends Controller
{
    use UpdatesScoutReports;

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
        dd($request->validated('dead_mobs'));
        if ($request->has('dead_mobs')) {
            // Parse mobs that are alive first
            // @todo
            $scout->dead_mobs()->upsert($request->validated('dead_mobs'), ['mob_id', 'instance_number']);
        }
        return [];
    }
}
