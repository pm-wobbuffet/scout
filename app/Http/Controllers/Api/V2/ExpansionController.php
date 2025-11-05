<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\ExpansionCollection;
use App\Http\Resources\Api\V2\ExpansionResource;
use App\Http\Resources\Api\V2\ZoneCollection;
use App\Models\Expansion;
use App\Models\Zone;
use Illuminate\Http\Request;

class ExpansionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $expansions = Expansion::query()
            ->withCount(['zones'])
            ->orderBy('id')
            ->get();
        return new ExpansionCollection($expansions);
    }

    /**
     * Display the specified resource.
     */
    public function show(Expansion $expansion)
    {
        $expansion->loadCount('zones');
        return new ExpansionResource($expansion);
    }

    /**
     * Display the list of zones attached to an expansion
     * @param \App\Models\Expansion $expansion
     * @return ZoneCollection
     */
    public function zones(Expansion $expansion): ZoneCollection
    {
        $zones = $expansion->zones()->withCount('spawn_points')->get();
        return new ZoneCollection($zones);
    }
}
