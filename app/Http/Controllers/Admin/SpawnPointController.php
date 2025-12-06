<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpawnPoint;
use App\Models\Zone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpawnPointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Zone $zone)
    {
        $zone->load(['spawn_points']);
        $points = $zone->spawn_points;

        return Inertia::render('admin/SpawnPoints/Index', [
            'zone' => $zone,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

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
    public function show(SpawnPoint $spawnPoint)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SpawnPoint $spawnPoint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SpawnPoint $spawnPoint)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SpawnPoint $spawnPoint)
    {
        //
    }
}
