<?php

namespace App\Http\Controllers;

use App\Models\Expansion;
use App\Models\Zone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MapController extends Controller
{
    public function index(Request $request, ?Zone $zone)
    {
        // Get latest expansion in the DB for display
        $expac = Expansion::orderBy('id', 'DESC')
            ->with(['zones', 'zones.aetherytes'])
            ->first();

        $selected_zone = $zone->id ?? $expac->zones->first()->id;

        return Inertia::render('maps/Index', [
            'expac' => $expac,
            'selected_zone' => $selected_zone,
        ]);
    }
}
