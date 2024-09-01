<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportPointsRequest;
use App\Http\Requests\StoreScoutRequest;
use App\Http\Requests\UpdatePointOccupiedRequest;
use App\Http\Requests\UpdateScoutMetaRequest;
use App\Http\Requests\UpdateScoutRequest;
use App\Models\CustomPoint;
use App\Models\Expansion;
use App\Models\Scout;
use App\Models\ScoutUpdate;
use App\Models\Zone;
use App\Traits\HandlesScoutUpdates;
use App\Traits\Traits\HandlesCustomPoints;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Sqids\Sqids;

class MainController extends Controller
{
    use HandlesScoutUpdates, HandlesCustomPoints;

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
     * @return \Inertia\Response | \Illuminate\Http\Response | \Illuminate\Http\JsonResponse
     */
    public function view(Request $request, Scout $scout, string $password = ''): \Inertia\Response | \Illuminate\Http\Response | \Illuminate\Http\JsonResponse
    {
        $scout->load(['updates']);
        $scout->loadMax('updates', 'id');
        if($password && $password === $scout->collaborator_password) {
            $scout->makeVisible(['collaborator_password']);
        }

        $expansions = $this->getExpansionsData();

        $exp_totals = $this->calculateExpTotals($expansions, $scout);
        if($request->wantsJson() || $request->has('json')) {
            return response()->json($this->generateJson($scout, $password === $scout->collaborator_password));
        }
        if($scout->title) {
            $this->setOGTitle($scout->title . ' ' . implode(', ', $exp_totals));
        } else {
            $this->setOGTitle(implode(', ', $exp_totals));
        }
        if($scout->scouts && sizeof($scout->scouts) > 0) {
            $this->setOGDescription('Scouted by: ' . implode(', ', $scout->scouts ?? []));
        }

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
        $this->saveScoutUpdate($request, $scout);
        // Delete any previous custom points added here
        $this->deleteCustomPointsForScout($scout, $request->input('point')['id']);
        // Process the actual update and assign the mob to the point_data array
        $this->handleScoutUpdate($request, $scout);

        return [
            'point_data'    => $scout->point_data,
            'custom_points' => $scout->custom_points,
            'finalized_at'  => $scout->finalized_at,
            'title'         => $scout->title,
            'scouts'        => $scout->scouts,
            'mob_status'    => $scout->mob_status,
        ];
    }

    public function updateOccupiedPoint(UpdatePointOccupiedRequest $request, Scout $scout, string $password = '')
    {
        if(!$password || $password !== $scout->collaborator_password) {
            abort('403', 'Method not allowed');
        }
        $point_id = $request->validated('point_id');
        $instance = $request->validated('instance_number');
        $status = $request->validated('status');
        $p = $scout->occupied_points;
        if(!isset($p[$point_id])) {
            $p[$point_id] = [];
        }
        $p[$point_id][$instance] = $status;
        $scout->occupied_points = $p;
        $scout->save();

        return [
            'occupied_points' => $scout->occupied_points,
        ];
    }

    public function updateMeta(UpdateScoutMetaRequest $request, Scout $scout, string $password = '')
    {
        if(!$password || $password !== $scout->collaborator_password) {
            abort('403', 'Method not allowed');
        }

        $scout->title = $request->safe()->input('title');
        $scout->scouts = $request->safe()->input('scouts');

        $scout->save();
        return [
            'point_data'    => $scout->point_data,
            'custom_points' => $scout->custom_points,
            'finalized_at'  => $scout->finalized_at,
            'title'         => $scout->title,
            'scouts'        => $scout->scouts,
            'mob_status'    => $scout->mob_status,
        ];
    }

    public function updateMobStatus(Request $request, Scout $scout, string $password = '') {
        if(!$password || $password !== $scout->collaborator_password) {
            abort('403', 'Method not allowed');
        }
        $request->validate([
            'mob_id'            =>  'required|integer',
            'instance_number'   =>  'required|integer',
            'status'            =>  'required|boolean',
        ]);
        if(!$scout->mob_status) {
            $scout->mob_status = [];
        }
        $mobs = $scout->mob_status;
        if($request->status == false) {
            // The mob is no longer marked as dead; remove it from the mob_status array if it's there
            if(isset($mobs[$request->mob_id][$request->instance_number])) {
                unset($mobs[$request->mob_id][$request->instance_number]);
            }
        } elseif($request->status) {
            if(!isset($mobs[$request->mob_id])) {
                $mobs[$request->mob_id] = [];
            }
            $mobs[$request->mob_id][$request->instance_number] = 1;
        }
        $scout->mob_status = $mobs;
        $scout->save();

        return [
            'point_data'    => $scout->point_data,
            'custom_points' => $scout->custom_points,
            'finalized_at'  => $scout->finalized_at,
            'title'         => $scout->title,
            'scouts'        => $scout->scouts,
            'mob_status'    => $scout->mob_status,
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
                $s[$zone_id][$instance] = array_values(array_filter($subs,function($item) use($mobById) {
                    if(isset($mobById[$item['mob_id']])) {
                        return false;
                    }
                    return true;
                }));
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

    /**
     * Extract custom points and create a custom point database entry
     * Custom points will always be identified by having an id < 0
     *
     * @param array $request
     * @param Scout $scout
     * @return void
     */
    private function extractCustomPoints(array $request, Scout $scout ): void
    {
        foreach($request['point_data'] as $zoneId => $instances) {
            foreach($instances as $instance) {
                foreach($instance as $mob) {
                    if($mob['point_id'] < 0 && isset($mob['line'])) {
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

    private function generateJson(Scout $scout, $is_collaborator = false) {
        $r = [
            'scout_id'          => $scout->id,
            'instance_counts'   => $scout->instance_data,
            'mob_list'          => $scout->point_data,
        ];
        if($is_collaborator) {
            $r['collaborator_password'] = $scout->collaborator_password;
        }
        return $r;
    }
}
