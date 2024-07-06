<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SpawnPoint;
use Illuminate\Http\Request;

class SpawnPointsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sp = SpawnPoint::query()
        ->with(['zone', 'zone.expansion', 'valid_mobs'])
        ->orderBy('zone_id')
        ->orderBy('x')
        ->orderBy('y')
        ->get();

        return $sp;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

}
