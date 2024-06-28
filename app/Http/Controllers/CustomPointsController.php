<?php

namespace App\Http\Controllers;

use App\Models\Expansion;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CustomPointsController extends Controller
{

    public function index()
    {
        $expac = Expansion::query()
            ->with(
                [
                    'zones',
                    'zones.mobs' => function ($query) {
                        $query->select(['id', 'name', 'rank', 'mob_index', 'zone_id', 'names']);
                    },
                    'zones.aetherytes',
                ]
            )
            ->where('id', '=', '7')
            ->get()->first();

        return Inertia::render('CustomPoints/Index', [
            'expac'     =>  $expac,
        ]);
    }


    public function get_custom(Request $request, Zone $zone)
    {
        $request->validate([
            'rounding' => [
                'numeric',
                Rule::in(['0.1', '0.5', '1', '2']),
            ],
        ]);

        $multipliers = [
            '0.1'   => ['mult' => 1, 'div' => 1],
            '0.5'   => ['mult' => 2, 'div' => 1],
            '1'     => ['mult' => 1, 'div' => 0],
            '2'     => ['mult' => .5, 'div' => 0],
        ];
        $mult = $multipliers[$request->input('rounding')] ?? 1;
        $onlychat = boolval($request->input('chatonly', false)) === true ?
        " AND line_source IS NOT NULL " : '';

        //dd($mult);
        if ($request->input('rounding') == "0.1") {
            $query = DB::select("
                SELECT zone_id,
                x as agg_x,
                y as agg_y,
                COUNT(*) as num_points
                FROM custom_points
                WHERE zone_id = ?
                $onlychat
                GROUP by zone_id,
                agg_x,
                agg_y
        ", [
                $zone->id,
            ]);
        } else {
            $query = DB::select("
                SELECT zone_id,
                ROUND(ROUND(x * {$mult['mult']})/{$mult['mult']}, {$mult['div']}) as agg_x,
                ROUND(ROUND(y * {$mult['mult']})/{$mult['mult']}, {$mult['div']}) as agg_y,
                COUNT(*) as num_points
                FROM custom_points
                WHERE zone_id = ?
                $onlychat
                GROUP by zone_id,
                agg_x,
                agg_y
        ", [
                $zone->id,
            ]);
        }

        return $query;
    }

    public function generate_custom(Expansion $expansion)
    {
        //  instance 1, 2, 3 icons for later
        $unicode_map = [
            '1' => '', // instance 1
            '2' => '', // instance 2
            '3' => '', // instance 3
            't' => '', // zone prefix indicator
        ];
        $expansion->load([
            'zones',
            'zones.mobs',
            'zones.spawn_points',
            'zones.spawn_points.valid_mobs',
        ]);

        // Generate a randomized in-game style list of coords for an expansion
        $output = [];
        foreach($expansion->zones as $zone) {
            $zone_name = $zone->names['de'];
            for($i = 1; $i <= $zone->default_instances; $i++) {
                foreach($zone->mobs as $mob) {
                    $x = $this->getRandomCoordinate($zone);
                    $y = $this->getRandomCoordinate($zone);
                    $line = "{$mob->names['de']} {$unicode_map['t']}{$zone_name}{$unicode_map[$i]} ( $x  , $y ) Z: 0.3";
                    $output[] = $line;
                }
            }
        }
        return '<pre>' . join("\r\n", $output) . '</pre>';
    }

    private function getRandomCoordinate(Zone $zone)
    {
        return random_int(20, ($zone->max_coord_size - 2) * 10) / 10;
    }
}
