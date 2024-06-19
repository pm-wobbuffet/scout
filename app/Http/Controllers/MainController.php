<?php

namespace App\Http\Controllers;

use App\Models\Expansion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MainController extends Controller
{
    
    function index()
    {
        $expansions = Expansion::query()
        ->with(['zones', 'zones.mobs', 'zones.aetherytes', 'zones.spawn_points'])
        ->withCount(['zones', 'mobs'])
        ->orderBy('id')
        ->get();
        //dd($expansions->toArray());
        return Inertia::render('Index', [
            'expac' =>  $expansions,
        ]);
    }

    function store(Request $request)
    {
        dd($request->all());
    }
}
