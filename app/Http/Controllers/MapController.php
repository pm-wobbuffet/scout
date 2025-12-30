<?php

namespace App\Http\Controllers;

use App\Models\Expansion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MapController extends Controller
{
    public function index(Request $request)
    {
        // Get latest expansion in the DB for display
        $expac = Expansion::orderBy('id', 'DESC')
            ->with(['zones', 'zones.aetherytes'])
            ->first();

        $selected_zone = $expac->zones->first()->id;

        return Inertia::render('maps/Index', [
            'expac' => $expac,
            'selected_zone' => $selected_zone,
        ]);
    }
}
