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
            ->with(['zones', 'zones.aetherytes', 'zones.mobs'])
            ->first();

        $selected_zone = $zone->id ?? $expac->zones->first()->id;
        if ($zone === null) {
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

    private function getPointsForZone(Zone $zone, float $round_factor = 0.1, ?int $mobid = null)
    {
        $query = DB::table('scout_points')
            ->selectRaw('zone_id, COUNT(*) as num_points,
            ROUND(x / ?) * ? as agg_x,
            ROUND(y / ?) * ? as agg_y', [
                $round_factor,
                $round_factor,
                $round_factor,
                $round_factor
            ])
            ->where('zone_id', $zone->id)
            ->where('point_type', '=', 'custom_spawn_point')
            ->whereNotNull('x')
            ->whereNotNull('y')
            ->when($mobid !== null, function ($query) use ($mobid) {
                $query->where('mob_id', '=', $mobid);
            })
            ->groupBy('zone_id', 'agg_x', 'agg_y')
            ->orderBy('num_points', 'DESC');

        return $query->get();
    }
}
