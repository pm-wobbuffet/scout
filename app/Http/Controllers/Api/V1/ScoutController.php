<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkUpdateScoutAPIRequest;
use App\Http\Requests\StoreScoutRequest;
use App\Http\Requests\UpdateScoutAPIRequest;
use App\Http\Requests\UpdateScoutRequest;
use App\Models\Scout;
use App\Traits\HandlesScoutUpdates;
use App\Traits\Traits\HandlesCustomPoints;
use Illuminate\Http\Request;

class ScoutController extends Controller
{
    use HandlesScoutUpdates, HandlesCustomPoints;

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
        //dd($request->safe()->all());
        $s = Scout::create($request->safe()->all());
        if($s) {
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
    public function update(UpdateScoutAPIRequest $request, Scout $scout)
    {
        // Delete any previous custom points added here
        $this->deleteCustomPointsForScout($scout, $request->input('point')['id']);
        // Make sure all updates are reflected in the current point_data
        //$this->syncPointDataWithUpdates($scout, $request);
        // Store the update sent
        $this->saveScoutUpdate($request, $scout);
        // Process the actual update and assign the mob to the point_data array
        $this->handleScoutUpdate($request, $scout);

        return response()->json([
            'point_id'              =>  $request->safe()->input('point_id'),
            'scout_id'              =>  $scout->slug,
            'collaborator_password' =>  $scout->collaborator_password,
            'readonly_url'          =>  route('scout.view', $scout),
            'collaborate_url'       =>  route('scout.view', [$scout, $scout->collaborator_password]),
        ]);
    }

    public function bulkUpdate(BulkUpdateScoutAPIRequest $request, Scout $scout)
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
