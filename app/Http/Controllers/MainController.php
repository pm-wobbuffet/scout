<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScoutRequest;
use App\Http\Requests\UpdateScoutRequest;
use App\Models\Expansion;
use App\Models\Scout;
use App\Models\ScoutUpdate;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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

        $exp_totals = $this->calculateExpTotals($expansions, $scout);
        $this->setOGTitle(implode(', ', $exp_totals));
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
        return $scout;
    }

    private function calculateExpTotals(EloquentCollection $expansions, Scout $scout): array
    {
        $ret = [];
        //dd($expansions->toArray(), $scout->toArray());
        $instances = $scout->instance_data;
        foreach($expansions as $expac) {
            $total_mobs = 0;
            $seen_mobs = 0;
            $expac->zones->each(function($item) use(&$total_mobs, &$seen_mobs, $scout, $instances) {
                //$total_mobs += $item->total_mobs;
                $total_mobs += $item->mobs->count() * $instances[$item->id];
                //$seen_mobs += count($scout['point_data'][$item->id] ?? []) ?? 0;
                if(isset($scout->point_data[$item->id])) {
                    // There are scouted instances
                    foreach($scout->point_data[$item->id] as $instance => $moblist) {
                        $seen_mobs += count($moblist);
                    }
                }
            });
            if($seen_mobs > 0) {
                $ret[] = "{$expac->abbreviation}: {$seen_mobs}/{$total_mobs}";
            }
        }
        return $ret;
    }
}
