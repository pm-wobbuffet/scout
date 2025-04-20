<?php

namespace App\Traits;

use App\Models\Zone;


trait CalculatesNearestPoint
{
    /**
     * Return the closest spawn point to the given x, y coordinate in a zone.
     * Also returns the distance calculated to the spawn point, in case the ability to add
     * custom spawn points via API ends up being supported.
     * @param \App\Models\Zone $zone
     * @param float $x
     * @param float $y
     * @return array{point: SpawnPoint, distance: float}
     */
    private function findClosestSpawnPoint(Zone $zone, float $x, float $y): array
    {
        $closest = null;
        $min_found = PHP_FLOAT_MAX;
        foreach($zone->spawn_points as $spawn_point) {
            $distance = pow($x - $spawn_point->x, 2) + pow($y - $spawn_point->y, 2);
            if($distance < $min_found) {
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
