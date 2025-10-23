<?php

namespace App\Traits;

use App\Events\Scout\MetaUpdated;
use App\Models\Scout;
use App\Models\ScoutCustomPoint;

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

    public function addScouterToScoutReport(Scout $scout, string $reporter)
    {
        if (!$reporter || $reporter === '') return;
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
}
