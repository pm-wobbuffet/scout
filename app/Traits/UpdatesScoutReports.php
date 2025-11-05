<?php

namespace App\Traits;

use App\Events\Scout\MetaUpdated;
use App\Models\Scout;
use App\Models\ScoutCustomPoint;
use Illuminate\Support\Facades\Log;

trait UpdatesScoutReports
{
    public function authorizeUpdate(Scout $scout, string $password)
    {
        if (!$password || ($scout->collaborator_password !== $password)) {
            abort(403, 'Unauthorized action.');
        }

        if ($scout && $scout->finalized_at !== null) {
            abort(403, 'Unauthorized action');
        }
    }

    public function addScouterToScoutReport(Scout $scout, ?string $reporter)
    {
        if ($reporter === null || !$reporter || $reporter === '') return;
        // If they already exist in the scout list, can return early
        if ($scout->scouts()->where('scout_name', '=', $reporter)->count() > 0) return;
        $scout->scouts()->create(['scout_name' => $reporter]);
        broadcast(new MetaUpdated(
            $scout,
            $scout->title,
            $scout->scouts,
        ));
    }

    public function handleCustomPoints(Scout $scout, $custom_points)
    {
        $pts = [];
        if (!$custom_points or !is_array($custom_points)) return $pts;
        foreach ($custom_points as $point) {
            // If ID < 0, it's a custom point that's not been processed.
            if (intval($point['id']) < 0) {
                $p = new ScoutCustomPoint();
                $p->x = $point['x'];
                $p->y = $point['y'];
                $p->zone_id = $point['zone_id'];
                $p->scout_id = $scout->id;
                $p->internal_id = $point['id']; // Save the mapping
                $p->save();
                $pts[$point['id']] = $p->id;
            }
        }
        return $pts;
    }

    public function createBulkUpdate(Scout $scout, array $sightings): array
    {
        // Key-based hash of zones that were updated as part of this report
        // Used to send a websocket message to clients that were listening
        $updated_zones = [];
        foreach ($sightings as $sighting) {
            Log::info("Sighting loop", ['sighting' => $sighting]);
            // Clear any previous sightings on this point
            $scout->points()->where('point_id', $sighting['point_id'])
                ->where('point_type', 'spawn_point')
                ->where('instance_number', $sighting['instance_number'])
                ->delete();
            // Clear any previous assignments for the mob on this zone
            $scout->points()->where('mob_id', $sighting['mob_id'])
                ->where('instance_number', $sighting['instance_number'])
                ->delete();
            // Remove the mob from any dead mob lists
            $scout->dead_mobs()->where('mob_id', $sighting['mob_id'])
                ->where('instance_number', $sighting['instance_number'])
                ->delete();

            $updated_zones[$sighting['zone_id'] . '-' . $sighting['instance_number']] = 1;
            $scout->points()->create([
                'point_type'        => 'spawn_point',
                'point_id'          => $sighting['point_id'],
                'instance_number'   => $sighting['instance_number'],
                'x'                 => $sighting['x'] ?? null,
                'y'                 => $sighting['y'] ?? null,
                'zone_id'           => $sighting['zone_id'],
                'mob_id'            => $sighting['mob_id'],
            ]);

            /*
            $this->createAtomicUpdateFromBulk($sighting, $scout);
            */
        }
        return $updated_zones;
    }

    private function createAtomicUpdateFromBulk($sighting, Scout $scout)
    {
        /*
        $up = new ScoutUpdate($sighting);
        $up->previous_instance_data = $scout->instance_data;
        $up->previous_point_data = $scout->point_data;
        $up->previous_custom_points = $scout->custom_points;
        $up->scout_id = $scout->id;
        $up->x = $sighting['x'];
        $up->y = $sighting['y'];
        $up->mob_index = $sighting['mob']['mob_index'] ?? '';
        $up->point_id = $sighting['point']['id'];
        $up->save();
        */
    }

    public function removeExistingScoutPoints(Scout $scout, $details)
    {
        // TODO: stub to try and keep this in one place for multiple scripts
    }
}
