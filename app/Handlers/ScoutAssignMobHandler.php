<?php
namespace App\Handlers;

use App\Models\Scout;
use App\Models\ScoutPoint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use stdClass;

class ScoutAssignMobHandler {

    public function handle(stdClass $message)
    {
        Validator::make((array) $message,[
            'slug'                  => 'required|string',
            'collaborator_password' => 'required|string',
            'point_id'              => 'numeric|required',
            'mob_id'                => 'numeric|required',
            'instance_number'       => 'numeric|required',
            'zone_id'               => 'numeric|required',
            'point_type'            => 'numeric|required',
        ]);
        $scout = Scout::whereSlug($message->slug)->firstOrFail();
        // Make sure they're actually a collaborator
        if($scout->collaborator_password != $message->collaborator_password) {
            return false;
        }
        // No edits allowed after finishing
        if($scout->finalized_at !== null) {
            return false;
        }
        // Remove any previous mobs on the point given
        ScoutPoint::where('scout_id', $scout->id)
        ->where('point_id', $message->point_id)
        ->where('instance_number', $message->instance_number)
        ->where('point_type', $message->point_type)
        ->delete();
        // Add in the new mob on the point
        ScoutPoint::firstOrCreate([
            'scout_id'          => $scout->id,
            'point_type'        => $message->point_type,
            'point_id'          => $message->point_id,
            'zone_id'           => $message->zone_id,
            'instance_number'   => $message->instance_number,
        ],[
            'mob_id'            => $message->mob_id,
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
    }
}
