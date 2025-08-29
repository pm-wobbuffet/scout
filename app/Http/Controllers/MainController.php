<?php

namespace App\Http\Controllers;

use App\Events\Scout\MetaUpdated;
use App\Events\Scout\PointOccupancyChanged;
use App\Http\Requests\Scout\HandleImportedPointsRequest;
use App\Http\Requests\Scout\StoreScoutRequest;
use App\Http\Requests\Scout\UpdateMetaRequest;
use App\Http\Requests\Scout\UpdateOccupiedPointRequest;
use App\Http\Resources\ExpansionResource;
use App\Http\Resources\ScoutResource;
use App\Models\Expansion;
use App\Models\Scout;
use App\Traits\UpdatesScoutReports;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MainController extends Controller
{
    use UpdatesScoutReports;

    /**
     * Show a blank map for the user to start their scouting journey
     */
    public function index(): \Inertia\Response
    {
        $expansions = $this->getExpansionsData();

        return Inertia::render('Index', [
            'expac' => ExpansionResource::collection($expansions),
            // 'expac'     => $expansions,
            'defaultId' => intval(env('DEFAULT_EXPANSION_ID', 7)),
        ]);
    }

    public function view(Request $request, Scout $scout, string $password = ''): \Inertia\Response|\Illuminate\Http\JsonResponse
    {
        $scout->load(['updates', 'dead_mobs', 'instances', 'points', 'scouts']);
        $scout->loadMax('updates', 'id');
        if ($password && $password === $scout->collaborator_password) {
            $scout->makeVisible(['collaborator_password']);
        }
        // dd($scout->instances);

        $expansions = $this->getExpansionsData();
        $exp_totals = $this->calculateExpTotals($expansions, $scout);
        if ($request->wantsJson() || $request->has('json')) {
            return response()->json($this->generateJson($scout, $password === $scout->collaborator_password));
        }
        // Set OpenGraph settings for discord display
        $this->setOpenGraphDetails($scout, $exp_totals);

        return Inertia::render('scout/View', [
            'expac' => $expansions,
            'scout' => new ScoutResource($scout),
            'defaultId' => intval(env('DEFAULT_EXPANSION_ID', 7)),
        ]);
    }

    /**
     * Store a scouting report to the database
     * Sends the user to the newly created report on success
     * @param \App\Http\Requests\Scout\StoreScoutRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreScoutRequest $request)
    {
        $scout = Scout::create($request->all());
        if ($request->has('points')) {
            $scout->points()->createMany($request->validated('points'));
        }
        if ($request->has('instance_data')) {
            foreach ($request->validated('instance_data') as $zone_id => $instance_count) {
                $scout->instances()->attach($zone_id, ['instance_count' => $instance_count]);
            }
        }
        if ($request->has('dead_mobs')) {
            $scout->dead_mobs()->createMany($request->validated('dead_mobs'));
        }
        if ($request->has('scouts') && $request->validated('scouts') !== null) {
            $scout->scouts()->createMany($request->validated('scouts'));
        }
        return redirect()->route('scout.view', [$scout->slug, $scout->collaborator_password])
            ->with(['newly_created' => true]);
    }

    /**
     * Updates the metadata for a scout request, including title and scouter list
     * @param \App\Http\Requests\Scout\UpdateMetaRequest $request
     * @param \App\Models\Scout $scout
     * @param string $password
     * @return JsonResponse
     */
    public function updateMeta(UpdateMetaRequest $request, Scout $scout, string $password): JsonResponse
    {
        $this->authorizeUpdate($scout, $password);

        $scout->title = $request->validated('title');
        $scout->scouts()->delete();
        foreach ($request->validated('scouts') as $scouter) {
            $scout->scouts()->updateOrCreate([
                'scout_name' => $scouter['scout_name'],
            ]);
        }
        $scout->save();
        broadcast(new MetaUpdated(
            $scout,
            $scout->title,
            $scout->scouts,
        ))->toOthers();
        return response()->json(['success' => true]);
    }

    public function getUpdates(Request $request, Scout $scout, string $password = ''): ScoutResource
    {
        $this->authorizeUpdate($scout, $password);
        $scout->load(['updates', 'dead_mobs', 'instances', 'points']);
        $scout->loadMax('updates', 'id');

        return new ScoutResource($scout);
    }

    public function updateOccupiedPoint(UpdateOccupiedPointRequest $request, Scout $scout, string $password = '')
    {
        $this->authorizeUpdate($scout, $password);
        // If the request has a mob_id (which should be null, but explicitly set)
        // we're marking as occupied, else unoccupy a spot
        if ($request->has('mob_id')) {
            $scout->occupied_pts()->updateOrCreate([
                'point_type' => $request->validated('point_type'),
                'point_id' => $request->validated('point_id'),
                'instance_number' => $request->validated('instance_number', 1),
                'zone_id' => $request->validated('zone_id'),
            ], [
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'mob_id' => null,
            ]);
        } else {
            $scout->occupied_pts()
                ->where('point_type', $request->validated('point_type'))
                ->where('point_id', $request->validated('point_id'))
                ->where('instance_number', $request->validated('instance_number', 1))
                ->where('zone_id', $request->validated('zone_id'))
                ->delete();
        }
        $points = $scout->points
            ->where('zone_id', $request->validated('zone_id'))
            ->where('instance_number', $request->validated('instance_number', 1));
        broadcast(new PointOccupancyChanged(
            $scout,
            $points,
            $request->validated('zone_id'),
            $request->validated('instance_number', 1)
        ))
            ->toOthers();

        return response()->json($points);
    }

    public function handleImportedPoints(HandleImportedPointsRequest $request, Scout $scout, string $password = '')
    {
        $this->authorizeUpdate($scout, $password);
        // Remove any existing points that are associated with an entry in the zonelist submitted
        // since we're assuming the user provides a complete list of points in the zone now (occupied or A rank taken combined)
        $p = $scout->points()->whereIn(
            DB::raw("CONCAT(zone_id,'-',instance_number)"),
            $request->validated('zonelist')
        )->get(); // TODO: Change to delete() when i'm happy with the rest of this

    }

    /* Private methods */

    private function calculateExpTotals(EloquentCollection $expansions, Scout $scout): array
    {
        $ret = [];
        // dd($expansions->toArray(), $scout->toArray());
        $instances = $scout->instance_data;
        foreach ($expansions as $expac) {
            $total_mobs = 0;
            $seen_mobs = 0;
            $expac->zones->each(function ($item) use (&$total_mobs, &$seen_mobs, $scout, $instances) {
                // $total_mobs += $item->total_mobs;
                $total_mobs += $item->mobs->count() * ($instances[$item->id] ?? 1);
                // $seen_mobs += count($scout['point_data'][$item->id] ?? []) ?? 0;
                if (isset($scout->point_data[$item->id])) {
                    // There are scouted instances
                    foreach ($scout->point_data[$item->id] as $instance => $moblist) {
                        $seen_mobs += count($moblist);
                    }
                }
            });
            if ($seen_mobs > 0) {
                $ret[] = "{$expac->abbreviation}: {$seen_mobs}/{$total_mobs}";
            }
        }

        return $ret;
    }

    /**
     * Get a subset of expansion information for use on the main page
     */
    private function getExpansionsData(): array|EloquentCollection
    {
        return Expansion::query()
            ->with([
                'zones',
                'zones.mobs' => function ($query) {
                    $query->select(['id', 'name', 'rank', 'mob_index', 'zone_id', 'names']);
                },
                'zones.aetherytes',
                'zones.spawn_points',
                'zones.spawn_points.valid_mobs' => function ($query) {
                    $query->select(['mobs.id', 'name', 'mob_index', 'zone_id']);
                },
            ])
            ->orderBy('id')
            ->get();
    }

    /**
     * Return an array of data about a scout for use in JSON responses, if requested in a non-API way
     *
     * @param  bool  $is_collaborator  - also return the collab password if true
     */
    private function generateJson(Scout $scout, bool $is_collaborator = false): array
    {
        $r = [
            'scout_id' => $scout->id,
            'instance_counts' => $scout->instance_data,
            'mob_list' => $scout->point_data,
        ];
        if ($is_collaborator) {
            $r['collaborator_password'] = $scout->collaborator_password;
        }

        return $r;
    }

    /**
     * Add OpenGraph Meta details to the Meta package
     * Calls functions in the parent Controller class
     *
     * @param  array  $exp_totals  - Array of expansion mob totals ["ARR: 10/17", "HW: 12/12"]
     */
    private function setOpenGraphDetails(Scout $scout, array $exp_totals): void
    {
        if ($scout->title) {
            $this->setOGTitle($scout->title . ' ' . implode(', ', $exp_totals));
        } else {
            $this->setOGTitle(implode(', ', $exp_totals));
        }

        if ($scout->scouts && count($scout->scouts) > 0) {
            $this->setOGDescription('Scouted by: ' . implode(', ', $scout->scouts->pluck('scout_name')->toArray() ?? []));
        }
    }
}
