<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\SpawnPointCollection;
use App\Http\Resources\Api\V2\ZoneCollection;
use App\Http\Resources\Api\V2\ZoneResource;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    /**
     * Display all active zones.
     */
    public function index()
    {
        $zones = Zone::query()
            ->withCount(['mobs', 'spawn_points', 'aetherytes'])
            ->orderBy('id')
            ->get();

        return new ZoneCollection($zones);
    }

    /**
     * Display a single zone
     */
    public function show(Zone $zone, Request $request)
    {
        $zone->load(['mobs', 'aetherytes']);
        if (boolval($request->input('show_spawn_points', false)) == true) {
            $zone->load('spawn_points');
        }
        $zone->loadCount(['mobs', 'spawn_points', 'aetherytes']);
        return new ZoneResource($zone);
    }

    /**
     * Display the spawn points for a given Zone
     * @param \App\Models\Zone $zone
     * @return SpawnPointCollection
     */
    public function spawn_points(Zone $zone)
    {
        $points = $zone->spawn_points()
            ->with(['valid_mobs' => function ($query) {
                $query->select(['mob_id'])->pluck('mob_id');
            }])
            ->orderBy('id')->get();
        return new SpawnPointCollection($points);
    }
}
