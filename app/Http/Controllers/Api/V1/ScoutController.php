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
    public function update(BulkUpdateScoutAPIRequest $request, Scout $scout)
    {
        $this->createBulkUpdate($scout, $request->validated('sightings'));
        // Make sure to credit the user if a username was supplied
        if($request->has('update_user') && $request->input('update_user') !== 'Anonymous') {
            if(!in_array($request->input('update_user'), $scout->scouts)) {
                $scout->scouts = [...$scout->scouts, $request->input('update_user')];
            }
        }
        $scout->save();
        return response()->json([
            'scout_id'              =>  $scout->slug,
            'collaborator_password' =>  $scout->collaborator_password,
            'readonly_url'          =>  route('scout.view', $scout),
            'collaborate_url'       =>  route('scout.view', [$scout, $scout->collaborator_password]),
            'processed_sightings'   =>  $request->validated('sightings'),
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
