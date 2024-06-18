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
        ->with(['zones', 'zones.mobs', 'zones.aetherytes'])
        ->withCount(['zones', 'mobs'])
        ->orderBy('id')
        ->get();
        return Inertia::render('Index', [
            'expac' =>  $expansions,
        ]);
    }
}
