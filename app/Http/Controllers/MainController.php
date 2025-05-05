<?php

namespace App\Http\Controllers;

use App\Models\Expansion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MainController extends Controller
{
    /**
     * Show a blank map for the user to start their scouting journey
     *
     * @return \Inertia\Response
     */
    function index(): \Inertia\Response
    {
        $expansions = $this->getExpansionsData();
        return Inertia::render('Index', [
            'expac'     =>  $expansions,
            'defaultId' =>  intval(env('DEFAULT_EXPANSION_ID', 7)),
        ]);
    }



    /* Private methods */

    /**
     * Get a subset of expansion information for use on the main page
     * @return array|\Illuminate\Database\Eloquent\Collection
     */
    private function getExpansionsData(): array|Collection
    {
        return Expansion::query()
        ->with([
            'zones',
            'zones.mobs' => function($query) {
                $query->select(['id', 'name', 'rank', 'mob_index', 'zone_id', 'names']);
            },
            'zones.aetherytes',
            'zones.spawn_points',
            'zones.spawn_points.valid_mobs' => function($query) {
                $query->select(['mobs.id', 'name', 'mob_index', 'zone_id']);
            }])
        ->withCount(['zones', 'mobs'])
        ->orderBy('id')
        ->get();
    }
}
