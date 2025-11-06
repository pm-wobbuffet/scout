<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\ScoutResource;
use App\Models\Scout;
use Illuminate\Http\Request;

class ScoutController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
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
    public function update(Request $request, Scout $scout)
    {
        //
    }
}
