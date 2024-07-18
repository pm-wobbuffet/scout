<?php

namespace App\Http\Requests;

use App\Models\Mob;
use App\Models\Scout;
use App\Models\SpawnPoint;
use App\Models\Zone;
use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateScoutAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $scout = $this->route('scout');
        if(!$scout) {
            return false;
        }
        // Check to make sure they supplied the correct collaborator_password
        // to prevent unauthorized users from supplying updates
        if($scout->collaborator_password !== $this->collaborator_password) {
            return false;
        }

        // Make sure we don't make any changes to an existing map that's finalized
        if(!is_null($scout->finalized_at)) {
            return false;
        }

        return true;
    }

    /**
     * Prepare the input array for validation
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // get a list of all zones so we can grab points to match
        /** @var $zones \App\Models\Zone[] */
        $zones = Zone::query()
        ->with(['mobs', 'spawn_points'])
        ->get()
        ->keyBy('id');

        $mod_sightings = [];

        for($i = 0; $i < sizeof($this->sightings); $i++)
        //foreach($this->sightings as $sighting)
        {
            $sighting = $this->sightings[$i];
            // If no instance number is specified, default to 1.
            // Unsure if this is good for the future, but I also chose to have
            // 1 as the default instance in the DB layout, rather than say, null for
            // when there are not multiple instances.
            if(!isset($sighting['instance_number'])) {
                $sighting['instance_number'] = 1;
            }

            // Check to see if the user supplied one of our pre-defined point IDs for
            // this sighting.
            if(!isset($sighting['point_id'])) {
                $point = $this->findClosestSpawnPoint($zones[$sighting['zone_id']], floatval($sighting['x']), floatval($sighting['y']));
                $sighting['point_id'] = $point['point']->id;
                $sighting['point'] = $point['point']->toArray();
                $sighting['distance'] = $point['distance'];
            } else {
                // Pull in point details
                $sighting['point'] = SpawnPoint::where('id', $sighting['point_id'])->first()->toArray();
            }
            // The javascript client posts a mob_index, but for ease of use, allow
            // API clients to simply specify the mob key from the NotoriousMonster table.
            if(!is_null($sighting['mob_id'])) {
                $sighting['mob'] = Mob::where('id', $sighting['mob_id'])->first()->toArray();
            }

            $mod_sightings[$i] = $sighting;
        }
        $this->merge([
            'sightings' =>  $mod_sightings,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'collaborator_password'             =>  'required',
            'sightings'                         =>  'required|array',
            'sightings.*.zone_id'               =>  'required|numeric',
            'sightings.*.instance_number'       =>  'numeric',
            'sightings.*.point_id'              =>  'numeric',
            'sightings.*.point'                 =>  'array',
            'sightings.*.x'                     =>  'required|numeric',
            'sightings.*.y'                     =>  'required|numeric',
            'sightings.*.mob_id'                =>  'numeric|nullable',
            'sightings.*.mob'                   =>  'array',
            'sightings.*.distance'              =>  'nullable|numeric',
        ];
    }


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
