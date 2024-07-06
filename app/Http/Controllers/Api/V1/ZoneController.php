<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $zones = Zone::query()
        ->with([
            'mobs',
            'spawn_points',
            'spawn_points.valid_mobs' => function($query) {
                $query->select(['mob_id', 'bNpcBase', 'mob_index']);
            },
        ])
        ->orderBy('sort_priority')
        ->get();

        return response()->json($zones);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

}
