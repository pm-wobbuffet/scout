<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\ScoutPointResource;
use App\Models\Scout;
use App\Models\ScoutPoint;
use Illuminate\Http\Request;

class ScoutPointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Scout $scout)
    {
        $scout->load(['points', 'points.mob']);
        return ScoutPointResource::collection($scout->points);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Scout $scout)
    {
        //
    }

    /**
     * Update a given ScoutPoint
     */
    public function update(Request $request, Scout $scout, ScoutPoint $scoutPoint)
    {
        //
    }

    /**
     * Remove a point from the ScoutPoint list
     */
    public function destroy(Scout $scout, ScoutPoint $scoutPoint)
    {
        //
    }
}
