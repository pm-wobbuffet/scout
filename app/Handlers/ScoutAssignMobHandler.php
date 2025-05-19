<?php
namespace App\Handlers;

use App\Events\ScoutAssignMobEvent;
use App\Models\Scout;
use App\Models\ScoutPoint;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use stdClass;

class ScoutAssignMobHandler {

    public function handle(stdClass $message)
    {
        $data = $message->data;
        Log::info(print_r($data, 1));
        $validator = Validator::make(json_decode(json_encode($data,true),true), [
            'slug'                  => 'required|string',
            'collaborator_password' => 'required|string',
            'point_id'              => 'numeric|required',
            'mob_id'                => 'numeric|required',
            'instance_number'       => 'numeric|required',
            'zone_id'               => 'numeric|required',
            'point_type'            => 'string|required',
        ]);
        if($validator->fails()) {
            Log::error($validator->errors()->first());
        }
        Log::info("Running handler in ScoutAssignMobHandler");
        $scout = Scout::whereSlug($data->slug)->firstOrFail();
        Log::info("Pulled scout data");
        // Make sure they're actually a collaborator
        if($scout->collaborator_password != $data->collaborator_password) {
            Log::error("Collaborator password mismatch");
            return false;
        }
        // No edits allowed after finishing
        if($scout->finalized_at !== null) {
            Log::error("Trying to update a finalized scouting report");
            return false;
        }
        // Remove any previous mobs on the point given
        Log::info('Preparing to delete previous point entries');
        ScoutPoint::where('scout_id', $scout->id)
        ->where('point_id', $data->point_id)
        ->where('instance_number', $data->instance_number)
        ->where('point_type', $data->point_type)
        ->delete();
        Log::info("Deleted previous thingies");
        // Add in the new mob on the point
        /*
        $sp = ScoutPoint::firstOrCreate([
            'scout_id'          => $scout->id,
            'point_type'        => $data->point_type,
            'point_id'          => $data->point_id,
            'zone_id'           => $data->zone_id,
            'instance_number'   => $data->instance_number,
        ],[
            'mob_id'            => $data->mob_id,
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        */
        Log::info("made it past point creation");
        //Log::info(message: print_r($sp, true));
        //broadcast(new ScoutAssignMobEvent($data));
        Log::info("Broadcasting message on {$message->channel}");
        
        Broadcast::on($message->channel)
        ->as('ScoutAssignMob')
        ->with(json_decode(json_encode($data,true),true)))
        ->toOthers()
        ->sendNow();
    }
}
