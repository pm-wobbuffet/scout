<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\MobCollection;
use App\Http\Resources\Api\V2\MobResource;
use App\Models\Mob;
use Illuminate\Http\Request;

class MobController extends Controller
{
    /**
     * Get a list of all Mobs
     */
    public function index()
    {
        $mobs = Mob::query()
            ->withCount(['spawn_points'])
            ->orderBy('id')
            ->get();
        return new MobCollection($mobs);
    }

    /**
     * Display the details of a single Mob
     */
    public function show(Mob $mob)
    {
        $mob->load(['spawn_points']);
        return new MobResource($mob);
    }
}
