<?php

namespace App\Http\Controllers;

use App\Http\Resources\ScoutResource;
use App\Models\Expansion;
use App\Models\Scout;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
            'defaultId' =>  intval(env('DEFAULT_EXPANSION_ID', 7)),
        ]);
    }

    public function view(Request $request, Scout $scout, string $password = ''): \Inertia\Response|\Illuminate\Http\JsonResponse
    {
        $scout->load(['updates', 'dead_mobs', 'instances', 'points']);
        $scout->loadMax('updates', 'id');
        if ($password && $password === $scout->collaborator_password) {
            $scout->makeVisible(['collaborator_password']);
        }
        //dd($scout->instances);

        $expansions = $this->getExpansionsData();
        $exp_totals = $this->calculateExpTotals($expansions, $scout);
        if ($request->wantsJson() || $request->has('json')) {
            return response()->json($this->generateJson($scout, $password === $scout->collaborator_password));
        }
        // Set OpenGraph settings for discord display
        $this->setOpenGraphDetails($scout, $exp_totals);

        return Inertia::render('scout/View', [
            'expac'     =>  $expansions,
            'scout'     =>  new ScoutResource($scout),
            'defaultId' =>  intval(env('DEFAULT_EXPANSION_ID', 7)),
        ]);
    }



    /* Private methods */

    private function calculateExpTotals(EloquentCollection $expansions, Scout $scout): array
    {
        $ret = [];
        //dd($expansions->toArray(), $scout->toArray());
        $instances = $scout->instance_data;
        foreach ($expansions as $expac) {
            $total_mobs = 0;
            $seen_mobs = 0;
            $expac->zones->each(function ($item) use (&$total_mobs, &$seen_mobs, $scout, $instances) {
                //$total_mobs += $item->total_mobs;
                $total_mobs += $item->mobs->count() * $instances[$item->id];
                //$seen_mobs += count($scout['point_data'][$item->id] ?? []) ?? 0;
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
     * @return array|\Illuminate\Database\Eloquent\Collection
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
                }
            ])
            ->withCount(['zones', 'mobs'])
            ->orderBy('id')
            ->get();
    }

    /**
     * Return an array of data about a scout for use in JSON responses, if requested in a non-API way
     *
     * @param Scout $scout
     * @param boolean $is_collaborator - also return the collab password if true
     * @return array
     */
    private function generateJson(Scout $scout, bool $is_collaborator = false): array
    {
        $r = [
            'scout_id'          => $scout->id,
            'instance_counts'   => $scout->instance_data,
            'mob_list'          => $scout->point_data,
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
     * @param Scout $scout
     * @param array $exp_totals - Array of expansion mob totals ["ARR: 10/17", "HW: 12/12"]
     * @return void
     */
    private function setOpenGraphDetails(Scout $scout, array $exp_totals): void
    {
        if ($scout->title) {
            $this->setOGTitle($scout->title . ' ' . implode(', ', $exp_totals));
        } else {
            $this->setOGTitle(implode(', ', $exp_totals));
        }
        if ($scout->scouts && sizeof($scout->scouts) > 0) {
            $this->setOGDescription('Scouted by: ' . implode(', ', $scout->scouts ?? []));
        }
    }
}
