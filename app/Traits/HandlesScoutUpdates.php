<?php

namespace App\Traits;

use App\Http\Requests\StoreScoutRequest;
use App\Http\Requests\UpdateScoutAPIRequest;
use App\Http\Requests\UpdateScoutRequest;
use App\Models\CustomPoint;
use App\Models\Scout;
use App\Models\ScoutUpdate;
use App\Models\Zone;
use Illuminate\Support\Collection;

trait HandlesScoutUpdates
{
    
    /**
     * Determine whether a given mob has already been assigned on a zone instance
     * @param \App\Http\Requests\UpdateScoutRequest|\App\Http\Requests\UpdateScoutAPIRequest $request
     * @param \App\Models\Scout $scout
     * @return bool
     */
    private function mobAlreadyExists(
        UpdateScoutRequest|UpdateScoutAPIRequest $request, 
        Scout $scout): bool
    {
        if($request->mob['mob_index'] == '') {
            // They're clearing out a point
            return false;
        }
        $z = $scout->point_data[$request->zone_id][$request->instance_number] ?? null;
        if(!$z) {
            return false;
        }
        foreach($z as $mob_point) {
            if($mob_point['mob_id'] == $request->mob['id']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create a Scout Update and persist it to the database.
     * Contains previous point/instance data for future rollback functionality
     * if I decide to implement it.
     * @param \App\Http\Requests\UpdateScoutAPIRequest|\App\Http\Requests\UpdateScoutRequest $request
     * @param \App\Models\Scout $scout
     * @return void
     */
    private function saveScoutUpdate(
        UpdateScoutAPIRequest|UpdateScoutRequest $request,
        Scout $scout
    ) {
        $up = new ScoutUpdate($request->safe()->all());
        $up->previous_instance_data = $scout->instance_data;
        $up->previous_point_data = $scout->point_data;
        $up->previous_custom_points = $scout->custom_points;
        $up->scout_id = $scout->id;
        $up->x = $request->input('point')['x'];
        $up->y = $request->input('point')['y'];
        $up->mob_index = $request->input('mob')['mob_index'] ?? '';
        $up->point_id = $request->input('point')['id'];
        $up->save();
    }

    private function handleScoutUpdate(
        UpdateScoutAPIRequest|UpdateScoutRequest $request,
        Scout $scout
    ) {
        $zone = Zone::where('id', $request->safe()->input('zone_id'))->first();
        $expac_id = $zone->expansion_id;
        $points = $scout->point_data;

        // Get or initialize a blank array of mobs assigned to this zone+instance
        $s = $points[$request->safe()->input('zone_id')][$request->safe()->input('instance_number')] ?? [];

        if(sizeof($s) > 0) {
            // Points already exist, need to filter out this point and we can fill it with a new mob if necessary
            // Use array values to keep it from screwing up if a mob is deleted
            // since array_filter preserves keys by default, so if you delete the 0-index mob, you would end up with
            // an array like ["1" => mob_details], which would break the JSON transform, creating an Object instead
            // of an array.
            $s = array_values(array_filter($s, function($value) use ($request) {
                if($value['point_id'] == $request->safe()->input('point')['id']) {
                    return false;
                }
                // Make sure the mob itself is also removed if it was already assigned
                // This prevents errors from API calls where javascript logic isn't checking this
                // ahead of time on the client side
                if( is_array($request->safe()->input('mob'))
                    && $request->safe()->input('mob')['mob_index'] != ''
                    && $value['mob_id'] == $request->safe()->input('mob')['id'] ) 
                {
                    return false;
                }
                return true;
            }));
        }

        // Check to see if they're actually assigning a new mob
        // If NULL is passed, they're blanking out the currently assigned point so
        // we just skip this
        if( 
            !is_null($request->safe()->input('mob')['mob_index'] ?? null) 
            && $request->safe()->input('mob')['mob_index'] != ''
        )
        {
            // Add the point to the list
            $s[] = [
                'x'             => $request->safe()->input('point')['x'],
                'y'             => $request->safe()->input('point')['y'],
                'mob_id'        => $request->safe()->input('mob')['id'],
                'zone_id'       => $zone->id,
                'point_id'      => $request->safe()->input('point')['id'],
                'expansion_id'  => $expac_id,
            ];
            if($request->input('point')['id'] < 0) {
                // This was a custom point, so throw it in the CustomPoints table
                $this->createCustomPointLogEntry($request, $scout, $zone);
            }
        }

        // Reassign the new mob assignment array to the zone/instance and persist to the scout model
        // Have to do it in two steps, since PHP won't let you reassign an array accessed by Laravel's magic
        // methods
        $points[$request->safe()->input('zone_id')][$request->safe()->input('instance_number')] = $s;
        $scout->point_data = $points;

        // Make sure any custom points we have in the DB but the client didn't submit are sent back to them
        // since another user submitted a point in a different window
        $custom_points = (new Collection($scout->custom_points))->concat($request->safe()->input('custom_points', []))
        ->unique('id')->values()->all();
        $scout->custom_points = $custom_points;
        $scout->save();
    }

    /**
     * For a given scouting report, cycle back through the update log and ensure that all points
     * sent via update are included in the point_data.
     * This is hopefully to prevent race conditions from deleting known points from a given report
     * if multiple updates are posted/patched quickly
     * @param \App\Models\Scout $scout
     * @param StoreScoutRequest|UpdateScoutAPIRequest|UpdateScoutRequest $request,
     * @return void
     */
    private function syncPointDataWithUpdates(
        Scout $scout,
        StoreScoutRequest|UpdateScoutAPIRequest|UpdateScoutRequest $request,
    ) {
        $updates = ScoutUpdate::where('scout_id', '=', $scout->id)
        ->orderByDesc('id')
        ->get();
        // Keep track of points seen in each zone/instance so as we cycle back
        // we can ignore previous entries that would have been superseeded by a later one
        $points_seen = [];
        foreach($updates as $update) {

        }
    }

    private function createBulkUpdate(
        Scout $scout,
        array $sightings
    ) {
        $points = $scout->point_data;
        foreach($sightings as $sighting)
        {
            // Get or initialize a blank array of mobs assigned to this zone+instance
            $s = $points[$sighting['zone_id']][$sighting['instance_number']] ?? [];
            if(sizeof($s) > 0) {
                // Need to remove existing mobs/points that are in this sighting
                $s = array_values(array_filter($s, function($value) use ($sighting) {
                    // If this point was already used, clear it out
                    if($value['point_id'] == $sighting['point']['id']) {
                        return false;
                    }
                    // Make sure the mob itself is also removed if it was already assigned
                    // This prevents errors from API calls where javascript logic isn't checking this
                    // ahead of time on the client side
                    if( is_array($sighting['mob'])
                        && $sighting['mob']['mob_index'] != ''
                        && $value['mob_id'] == $sighting['mob']['id'] ) 
                    {
                        return false;
                    }
                    return true;
                }));
            }

            // Is a new mob being assigned?
            if(
                !is_null($sighting['mob']['mob_index'])
                && $sighting['mob']['mob_index'] != ''
            ) {
                $s[] = [
                    'x' => $sighting['x'],
                    'y' => $sighting['y'],
                    'mob_id' => $sighting['mob']['id'],
                    'point_id' => $sighting['point']['id'],
                ];
                if($sighting['point']['id'] < 0) {
                    // TODO: Handle custom point saving logic
                }
            }
            // Persist data
            $points[$sighting['zone_id']][$sighting['instance_number']] = $s;
            $this->createAtomicUpdateFromBulk($sighting, $scout);
        }
        $scout->point_data = $points;
        $scout->save();
    }

    private function createAtomicUpdateFromBulk($sighting, Scout $scout) {
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
    }
    private function createCustomPointLogEntry(
        StoreScoutRequest|UpdateScoutAPIRequest|UpdateScoutRequest $request,
        Scout $scout,
        Zone $zone
    ): void {
        CustomPoint::create([
            'scout_id'  => $scout->id,
            'zone_id'   => $zone->id,
            'point_id'  => $request->safe()->input('point')['id'],
            'x'         => $request->safe()->input('point')['x'],
            'y'         => $request->safe()->input('point')['y'],
            'mob_id'    => $request->safe()->input('mob')['id'],
        ]);
    }
}
