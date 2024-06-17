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
        ->with(['zones'])
        ->withCount(['zones'])
        ->orderBy('id')
        ->get();
        return Inertia::render('Index', [
            'expac' =>  $expansions,
        ]);
    }
}
