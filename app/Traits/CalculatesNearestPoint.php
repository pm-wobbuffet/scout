<?php

namespace App\Traits;

use App\Models\Scout;
use App\Models\ScoutCustomPoint;
use App\Models\Zone;
use Illuminate\Support\Facades\Log;

trait CalculatesNearestPoint
{

    private function getSpawnPointsForZone(Zone $zone, ?Scout $scout)
    {
        $spawn_pts = $zone->spawn_points;
        // Returned a combined list of normal and custom spawn points for a given scout report
        if ($scout !== null) {
            // Pull in any custom points that existed for this scouting report
            $custom = ScoutCustomPoint::where('scout_id', $scout->id)->get();
            foreach ($custom as $c_point) {
                $spawn_pts[] = $c_point;
            }
        }
        return $spawn_pts;
    }

    private function findClosestSpawnPoint($point_list, float $x, float $y): array
    {
        Log::info($point_list);
        $closest = null;
        $min_found = PHP_FLOAT_MAX;
        foreach ($point_list as $spawn_point) {
            $distance = pow($x - $spawn_point->x, 2) + pow($y - $spawn_point->y, 2);
            if ($distance < $min_found) {
                $min_found = $distance;
                $closest = $spawn_point;
            }
        }

        return [
            'point'     => $closest,
            'distance'  => sqrt($min_found),
        ];
    }
}
