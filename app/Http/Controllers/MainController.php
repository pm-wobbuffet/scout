<?php

namespace App\Http\Controllers;

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

    public function view(Request $request, Scout $scout, string $password = ''): \Inertia\Response
    {
        $scout->load(['updates']);
        $scout->loadMax('updates', 'id');
        if ($password && $password === $scout->collaborator_password) {
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
    }



    /* Private methods */

    private function calculateExpTotals( EloquentCollection $expansions, Scout $scout): array
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


    /**
     * Get a subset of expansion information for use on the main page
     * @return array|\Illuminate\Database\Eloquent\Collection
     */
    private function getExpansionsData(): array|Collection
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
}
