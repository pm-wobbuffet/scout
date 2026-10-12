<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomPointViewRequest;
use App\Models\Expansion;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    public function index(CustomPointViewRequest $request, ?Zone $zone): Response
    {
        // Get latest expansion in the DB for display
        $expac = Expansion::orderBy('id', 'DESC')
            ->with(['zones', 'zones.aetherytes', 'zones.mobs'])
            ->first();

        $selected_zone = $zone->id ?? $expac->zones->first()->id;
        if ($zone->id === null) {
            $zone = Zone::whereId($selected_zone)->first();
        }

        // $pts = $this->getPointsForZone($zone);

        return Inertia::render('maps/Index', [
            'expac'         => $expac,
            'selected_zone' => $selected_zone,
            'rounding'      => $request->validated('rounding', 0.1),
            'mobid'         => $request->validated('mobid'),
            'point_data'    => function () use ($zone, $request) {
                return $this->getPointsForZone(
                    $zone,
                    $request->validated('rounding'),
                    $request->validated('mobid')
                );
            }
        ]);
    }

    private function getPointsForZone(Zone $zone, float $round_factor = 0.1, ?int $mobid = null): Collection
    {

        $query = DB::table('scout_custom_points', 'scp')
            ->join('scout_points as sp', 'scp.scout_id', '=', 'sp.scout_id')
            ->selectRaw('scp.zone_id, COUNT(*) as num_points,
            ROUND(scp.x / ?) * ? as agg_x,
            ROUND(scp.y / ?) * ? as agg_y', [
                $round_factor,
                $round_factor,
                $round_factor,
                $round_factor
            ])
            ->where('scp.zone_id', '=', $zone->id)
            ->where('sp.point_type', '=', 'custom_spawn_point')
            ->whereRaw('scp.id = sp.point_id')
            ->whereNotNull('scp.x')
            ->whereNotNull('scp.y')
            ->when($mobid !== null, function ($query) use ($mobid) {
                $query->where('mob_id', '=', $mobid);
            })
            ->groupBy('scp.zone_id', 'agg_x', 'agg_y')
            ->orderBy('num_points', 'DESC');



        return $query->get();
    }
}
