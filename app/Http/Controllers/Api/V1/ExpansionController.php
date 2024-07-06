<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Expansion;
use Illuminate\Http\Request;

class ExpansionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expac = Expansion::query()
        ->with(['zones', 'zones.mobs', 'zones.spawn_points', 'zones.spawn_points.valid_mobs'])
        ->get();

        return response()->json($expac);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

}
