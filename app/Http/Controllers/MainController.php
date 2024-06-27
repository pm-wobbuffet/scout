<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportPointsRequest;
use App\Http\Requests\StoreScoutRequest;
use App\Http\Requests\UpdateScoutRequest;
use App\Models\CustomPoint;
use App\Models\Expansion;
use App\Models\Scout;
use App\Models\ScoutUpdate;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Sqids\Sqids;

class MainController extends Controller
{
    
    /**
     * Show a blank map for the user to start their scouting journey
     *
     * @return \Inertia\Response
     */
    function index(): \Inertia\Response
    {
        $expansions = $this->getExpansionsData();
        return Inertia::render('Index', [
            'expac'     =>  $expansions,
            'defaultId' =>  intval(env('DEFAULT_EXPANSION_ID', 6)),
        ]);
    }

    /**
     * Store a new scout map and send the user to the new map, with options for sharing
     *
     * @param StoreScoutRequest $request
     * @return RedirectResponse
     */
    public function store(StoreScoutRequest $request): RedirectResponse
    {
        //dd($request->all());
        $s = Scout::create($request->safe()->all());
        $this->extractCustomPoints($request->all(), $s);
        
        //return Inertia::location(route('scout.view', [$s->slug, $s->collaborator_password]));
        return redirect()->route('scout.view',[$s->slug, $s->collaborator_password])
        ->with(['newly_created' => true]);
    }

    /**
     * Show a single scout instance
     * If the user specified the proper collaborator password, allow editing
     *
     * @param Scout $scout
     * @param string $password
     * @return \Inertia\Response
     */
    public function view(Scout $scout, string $password = ''): \Inertia\Response
    {
        $scout->load(['updates']);
        $scout->loadMax('updates', 'id');
        if($password && $password === $scout->collaborator_password) {
            $scout->makeVisible(['collaborator_password']);
        }
        
        $expansions = $this->getExpansionsData();

        $exp_totals = $this->calculateExpTotals($expansions, $scout);
        $this->setOGTitle(implode(', ', $exp_totals));
        return Inertia::render('Scout/View',[
            'expac' =>  $expansions,
            'scout' =>  $scout,
            'defaultId' =>  intval(env('DEFAULT_EXPANSION_ID', 6)),
        ]);
    }

    /**
     * Process a mob update request
     *
     * @param UpdateScoutRequest $request
     * @param Scout $scout
     * @param string $password
     * @return array - updated point_data to replace current in View
     */
    public function update(UpdateScoutRequest $request, Scout $scout, string $password = ''): array
    {
        if(!$password || $password !== $scout->collaborator_password) {
            abort('403', 'Method not allowed');
        }

        if($this->mobAlreadyExists($request, $scout)) {
            // Prevent collisions by multiple users submitting the same mob
            return [
                'point_data' => $scout->point_data,
                'custom_points' => $scout->custom_points,
                'collision'     => true,
            ];
        }
        
        // Store the update sent
        $up = new ScoutUpdate($request->safe()->all());
        $up->previous_instance_data = $scout->instance_data;
        $up->previous_point_data = $scout->point_data;
        $up->previous_custom_points = $scout->custom_points;
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
            // Use array values to keep it from fucking up if a mob is deleted
            // since array_filter preserves keys by default
            $s = array_values(array_filter($s, function($value) use ($request) {
                if($value['point_id'] == $request->input('point')['id']) {
                    return false;
                }
                return true;
            }));
        }
        // Delete any previous custom points added here
        CustomPoint::query()
        ->where('scout_id', $scout->id)
        ->where('point_id', $request->input('point')['id'])
        ->delete();
        
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
            if($request->input('point')['id'] < 0) {
                // This was a custom point, so throw it in the CustomPoints table
                CustomPoint::create([
                    'scout_id'  => $scout->id,
                    'zone_id'   => $zone->id,
                    'point_id'  => $request->input('point')['id'],
                    'x'         => $request->input('point')['x'],
                    'y'         => $request->input('point')['y'],
                    'mob_id'    => $request->input('mob')['id'],
                ]);
            }
        }
        $points[$request->input('zone_id')][$request->input('instance_number')] = $s;
        $scout->point_data = $points;
        //$scout->point_data[$request->input('zone_id')][$request->input('instance_number')];

        // Make sure any custom points we have in the DB but the client didn't submit are sent back to them
        // since another user submitted a point in a different window
        $custom_points = (new Collection($scout->custom_points))->concat($request->safe()->input('custom_points'))
        ->unique('id')->values()->all();
        $scout->custom_points = $custom_points;
        $scout->save();

        return [
            'point_data'    => $scout->point_data,
            'custom_points' => $scout->custom_points,
            'finalized_at'  => $scout->finalized_at,
        ];
    }

    public function clone(Request $request, Scout $scout)
    {
        $sc = Scout::create([
            'instance_data' =>  $scout->instance_data,
            'point_data'    =>  $scout->point_data,
            'custom_points' =>  $scout->custom_points,
            'collaborator_password' => str(bin2hex(random_bytes(4))),
        ]);
        if($sc) {
            return redirect()->route('scout.view',[$sc->slug, $sc->collaborator_password])
                ->with(['newly_created' => true]);
        }

    }

    public function import(ImportPointsRequest $request, Scout $scout, string $password = '') {
        if(!$password || $password !== $scout->collaborator_password) {
            abort('403', 'Method not allowed');
        }
        $scout->instance_data = $request->safe()->input('instance_data');
        //Cycle through the existing custom points and make sure they're not added to the array twice
        $existing = (new Collection($scout->custom_points))->keyBy('id');
        foreach($request->safe()->input('custom_points') as $point) {
            if(!isset($existing[$point['id']])) {
                $existing[$point['id']] = $point;
            }
        }
        $scout->custom_points = $existing->values()->toArray();

        // Now we need to go through the points array and make sure we prevent any collisions
        $s = $scout->point_data;
        foreach($request->safe()->input('point_data') as $zone_id => $instances) {
            foreach($instances as $instance => $mobs) {
                $mobById = (new Collection($mobs))->keyBy('mob_id');
                // Filter out any existing mobs from this group that are already assigned to a point
                $subs = $s[$zone_id][$instance] ?? [];
                $s[$zone_id][$instance] = array_filter($subs,function($item) use($mobById) {
                    if(isset($mobById[$item['mob_id']])) {
                        return false;
                    }
                    return true;
                });
                // Add new mobs
                foreach($mobs as $mob) {
                    $s[$zone_id][$instance][] = $mob;
                }
            }
        }
        $scout->point_data = $s;
        //dd($scout->point_data, $s);
        $scout->save();
        $this->extractCustomPoints($request->all(), $scout);
        return [
            'point_data'    => $scout->point_data,
            'custom_points' => $scout->custom_points,
            'instance_data' => $scout->instance_data,
            'finalized_at'  => $scout->finalized_at,
        ];
        //dd($scout->custom_points->merge($request->safe()->input('custom_points')));
    }

    public function getUpdates(Request $request, Scout $scout, string $password = '')
    {
        if($password && $password !== $scout->collaborator_password) {
            abort('403', 'Method not allowed');
        }
        return $scout;
    }

    public function finalize(Scout $scout, string $password = '')
    {
        if($password && $password !== $scout->collaborator_password) {
            abort('403', 'Method not allowed');
        }
        $scout->update([
            'finalized_at'  =>  Carbon::now(),
        ]);

        return to_route('scout.view',[$scout, $password]);
        //return $scout;
    }


    /* Private utility methods */

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

    private function mobAlreadyExists(UpdateScoutRequest $request, Scout $scout)
    {
        if($request->mob['mob_index'] == '') {
            // They're clearing out a point
            return false;
        }
        $z = $scout->point_data[$request->zone_id][$request->instance_number] ?? null;
        if(!$z) {
            return false;
        }
        foreach($z as $mob_point) {
            if($mob_point['mob_id'] == $request->mob['id']) {
                return true;
            }
        }
        return false;
    }

    private function getExpansionsData()
    {
        return Expansion::query()
        ->with([
            'zones', 
            'zones.mobs' => function($query) {
                $query->select(['id', 'name', 'rank', 'mob_index', 'zone_id', 'names']);
            }, 
            'zones.aetherytes', 
            'zones.spawn_points', 
            'zones.spawn_points.valid_mobs' => function($query) {
                $query->select(['mobs.id', 'name', 'mob_index', 'zone_id']);
            }])
        ->withCount(['zones', 'mobs'])
        ->orderBy('id')
        ->get();
    }

    private function extractCustomPoints(array $request, Scout $scout ): void
    {
        foreach($request['point_data'] as $zoneId => $instances) {
            foreach($instances as $instance) {
                foreach($instance as $mob) {
                    if($mob['point_id'] < 0 || isset($mob['line'])) {
                        CustomPoint::create([
                            'scout_id'  => $scout->id,
                            'zone_id'   => $zoneId,
                            'point_id'  => $mob['point_id'],
                            'x'         => $mob['x'],
                            'y'         => $mob['y'],
                            'mob_id'    => $mob['mob_id'],
                            'line_source' => $mob['line'] ?? null,
                        ]);
                    }   
                }
            }
        }
    }
}
