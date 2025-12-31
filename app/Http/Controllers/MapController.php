<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomPointViewRequest;
use App\Models\Expansion;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MapController extends Controller
{
    public function index(CustomPointViewRequest $request, ?Zone $zone)
    {
        // Get latest expansion in the DB for display
        $expac = Expansion::orderBy('id', 'DESC')
            ->with(['zones', 'zones.aetherytes'])
            ->first();

        $selected_zone = $zone->id ?? $expac->zones->first()->id;
        if ($zone === null) {
            $zone = Zone::whereId($selected_zone)->first();
        }

        // $pts = $this->getPointsForZone($zone);

        return Inertia::render('maps/Index', [
            'expac'         => $expac,
            'selected_zone' => $selected_zone,
            'point_data'    => function () use ($zone, $request) {
                return $this->getPointsForZone($zone, $request->validated('rounding'));
            }
        ]);
    }

    private function getPointsForZone(Zone $zone, $round_factor = 0.1)
    {
        if ($round_factor == 0.1) {
            $query = DB::select("
            SELECT zone_id, x as agg_x, y as agg_y,
            COUNT(*) as num_points
            FROM scout_points sp 
            WHERE zone_id = ? AND sp.point_type ='custom_spawn_point'
            GROUP by zone_id, agg_x, agg_y
            ORDER BY num_points DESC
        ", [$zone->id]);
        } else {
            $query = DB::select("
            SELECT zone_id,
            ROUND(x / $round_factor) * $round_factor as agg_x,
            ROUND(y / $round_factor) * $round_factor as agg_y,
            COUNT(*) as num_points
            FROM scout_points sp
            WHERE zone_id = ? AND sp.point_type='custom_spawn_point'
            GROUP BY zone_id, agg_x, agg_y
            ", [$zone->id]);
        }

        return $query;
    }
}
