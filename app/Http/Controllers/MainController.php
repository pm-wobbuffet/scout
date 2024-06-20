<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScoutRequest;
use App\Http\Requests\UpdateScoutRequest;
use App\Models\Expansion;
use App\Models\Scout;
use App\Models\ScoutUpdate;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        
        //return Inertia::location(route('scout.view', [$s->slug, $s->collaborator_password]));
        return redirect()->route('scout.view',[$s->slug, $s->collaborator_password])
        ->with(['newly_created' => true]);
    }

    public function view(Scout $scout, string $password = '') 
    {
        $scout->load(['updates']);
        $scout->loadMax('updates', 'id');
        if($password && $password === $scout->collaborator_password) {
            $scout->makeVisible(['collaborator_password']);
        }
        
        $expansions = Expansion::query()
        ->with(['zones', 'zones.mobs', 'zones.aetherytes', 'zones.spawn_points'])
        ->withCount(['zones', 'mobs'])
        ->orderBy('id')
        ->get();

        return Inertia::render('Scout/View',[
            'expac' =>  $expansions,
            'scout' =>  $scout,
        ]);
    }

    public function update(UpdateScoutRequest $request, Scout $scout, string $password = '')
    {
        if(!$password || $password !== $scout->collaborator_password) {
            abort('403', 'Method not allowed');
        }
        
        // Store the update sent
        $up = new ScoutUpdate($request->safe()->all());
        $up->previous_instance_data = $scout->instance_data;
        $up->previous_point_data = $scout->point_data;
        $up->scout_id = $scout->id;
        $up->x = $request->input('point')['x'];
        $up->y = $request->input('point')['y'];
        $up->mob_index = $request->input('mob')['mob_index'] ?? '';
        $up->point_id = $request->input('point')['id'];
        $up->save();

        // If the mob has no index, we're clearing out the point
        $zone = Zone::where('id', $request->input('zone_id'))->first();
        $expac_id = $zone->expansion_id;
        $points = $scout->point_data;
        $s = $points[$request->input('zone_id')][$request->input('instance_number')] ?? [];
        if(sizeof($s) > 0) {
            // Points already exist, need to filter out this point and we can fill it with a new mob if necessary
            $s = array_filter($s, function($value) use ($request) {
                if($value['point_id'] == $request->input('point')['id']) {
                    return false;
                }
                return true;
            });
        }
        if( !is_null($request->input('mob')['mob_index']) ) 
        {
            // Add the point to the list
            $s[] = [
                'x'             => $request->input('point')['x'],
                'y'             => $request->input('point')['y'],
                'mob_id'        => $request->input('mob')['id'],
                'zone_id'       => $zone->id,
                'point_id'      => $request->input('point')['id'],
                'expansion_id'  => $expac_id,
            ];
        }
        $points[$request->input('zone_id')][$request->input('instance_number')] = $s;
        $scout->point_data = $points;
        $scout->point_data[$request->input('zone_id')][$request->input('instance_number')];
        $scout->save();

        return [
            'point_data' => $scout->point_data,
        ];
    }

    public function getUpdates(Request $request, Scout $scout, string $password = '')
    {
        if($password && $password !== $scout->collaborator_password) {
            abort('403', 'Method not allowed');
        }
        // $updates = ScoutUpdate::query()
        // ->where('scout_id', $scout->id)
        // ->when($request->input('last_id'), function($query, $lastid) {
        //     $query->where('id', '>', $lastid);
        // })
        // ->orderBy('id', 'asc')
        // ->get()
        // ;

        // return $updates;
        return $scout;
    }
}
