<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScoutRequest;
use App\Models\Expansion;
use App\Models\Scout;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Sqids\Sqids;

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

    public function store(StoreScoutRequest $request)
    {
        $s = Scout::create($request->safe()->all());
        //$s->collaborator_password = str(bin2hex(random_bytes(4)));
        //$s->save();   
        // Create an ID generator for the submission
        $sqids = new Sqids(minLength: 10, alphabet: env('SQID_ALPHABET'));
        $stub = $sqids->encode([$s->id]);
        $s->update([
            'slug'                  =>  $stub,
            'collaborator_password' =>  str(bin2hex(random_bytes(4))),
        ]);
        
        return Inertia::location(route('scout.view', [$s->slug, $s->collaborator_password]));
        //return redirect()->route('scout.view',[$s->slug, $s->collaborator_password]);
    }

    public function view(Scout $scout, string $password = '') 
    {
        if($password && $password === $scout->collaborator_password) {
            $scout->makeVisible(['collaborator_password']);
        }
        return $scout;
    }
}
