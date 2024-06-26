<?php

namespace App\Http\Controllers;

use App\Models\Expansion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomPointsController extends Controller
{
    
    public function index()
    {
        $expac = Expansion::query()
        ->with([
            'zones', 
            'zones.mobs' => function($query) {
                $query->select(['id', 'name', 'rank', 'mob_index', 'zone_id', 'names']);
            }, 
            'zones.aetherytes',
        ]
        )
        ->where('id', '=', '7')
        ->get()->first();

        return Inertia::render('CustomPoints/Index',[
            'expac'     =>  $expac,
        ]);
    }
}
