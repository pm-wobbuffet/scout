<?php

namespace App\Traits;

use App\Events\Scout\MetaUpdated;
use App\Events\ScoutAssignMob;
use App\Events\ScoutClearPoint;
use App\Events\ScoutReportModified;
use App\Models\Expansion;
use App\Models\Scout;
use App\Models\ScoutCustomPoint;
use App\Models\ScoutPoint;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
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
        //if ($scout->scouts()->where('scout_name', '=', $reporter)->count() > 0) return;
        $scout->scouts()->upsert([
            'scout_name' => $reporter,
        ], 'scout_name');

        $this->metaUpdated($scout);
    }

    public function metaUpdated(Scout $scout)
    {
        broadcast(new MetaUpdated($scout, $scout->title, $scout->scouts));
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
                $p->assigned_by_import = $point['assigned_by_import'] ?? 0;
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
        $points = [];
        foreach ($sightings as $sighting) {
            // Check to see if the point was already assigned by someone else.
            // If so, we can bail so we don't accidentally overwrite the original reporter
            $point = $scout->points()->where('point_id', $sighting['point_id'])
                ->where('point_type', 'spawn_point')
                ->where('instance_number', $sighting['instance_number'])
                ->where('mob_id', $sighting['mob_id'])
                ->first();
            if ($point && $point->count() > 0) {
                // This point was already assigned
                $points[] = $point;
            } else {
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

                $points[] = $scout->points()->create([
                    'point_type'        => 'spawn_point',
                    'point_id'          => $sighting['point_id'],
                    'instance_number'   => $sighting['instance_number'],
                    'x'                 => $sighting['x'] ?? null,
                    'y'                 => $sighting['y'] ?? null,
                    'zone_id'           => $sighting['zone_id'],
                    'mob_id'            => $sighting['mob_id'],
                ]);
            }
        }
        $this->sendReportModifiedEvent($scout, [
            'name' => "Mulitple Points Imported (" . sizeof($sightings) . ")"
        ]);
        return [
            'zones' => $updated_zones,
            'points' => $points
        ];
    }

    public function removeExistingScoutPoints(Scout $scout, $details)
    {
        // @TODO: stub to try and keep this in one place for multiple scripts
    }

    /**
     * Send a ScoutReportModified event to trigger creation of a new version
     * of this scouting report, if it is outside the lockout period of the 
     * previous version
     *
     * @param Scout $scout
     * @param array $details
     * @return void
     */
    public function sendReportModifiedEvent(Scout $scout, array $details)
    {
        // Do a quick search for the keys we normally submit for user updates
        // @todo: standardize this at some point in all the requests
        $user = request('update_user', request('reporter', null));
        event(new ScoutReportModified($scout, $details, $user));
    }

    public function sendScoutMobAssignedEvent(Scout $scout, int $zone_id, int $instance_number, ScoutPoint $point)
    {
        broadcast(
            new ScoutAssignMob(
                $scout,
                $zone_id,
                $instance_number,
                [$point],
                // $scout->points->where('zone_id', $zone_id)
                //     ->where('instance_number', $instance_number)->values()
            )
        )->toOthers();
    }

    public function sendPointClearedEvent(Scout $scout, $point_type, $point_id, $instance_number)
    {
        broadcast(
            new ScoutClearPoint($scout, $point_id, $point_type, $instance_number)
        )->toOthers();
    }

    /**
     * Get a subset of expansion information for use on the main page
     * @return array | Collection<int, Expansion>
     */
    public function getExpansionsData(): array | Collection
    {
        return Cache::remember('expansions-data', 30, function () {
            return Expansion::query()
                ->with([
                    'zones',
                    'zones.mobs' => function ($query) {
                        $query->select(['id', 'name', 'rank', 'mob_index', 'zone_id', 'names', 'bNpcBase']);
                    },
                    'zones.othermobs' => function ($query) {
                        $query->select(['id', 'name', 'rank', 'mob_index', 'zone_id', 'names', 'bNpcBase']);
                    },
                    'zones.aetherytes',
                    'zones.spawn_points',
                    'zones.spawn_points.valid_mobs' => function ($query) {
                        $query->select(['mobs.id', 'name', 'mob_index', 'zone_id']);
                    },
                ])
                ->orderBy('id')
                ->get();
        });
    }
}
