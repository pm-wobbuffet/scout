<?php

namespace App\Http\Requests;

use App\Models\Mob;
use App\Models\Scout;
use App\Models\SpawnPoint;
use App\Models\Zone;
use Illuminate\Foundation\Http\FormRequest;

class UpdateScoutAPIRequest extends FormRequest
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
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Grab a reference to the zone
        $zone = Zone::query()
        ->with(['mobs', 'spawn_points'])
        ->where('id', $this->zone_id)->firstOrFail();
        
        // If no instance number is specified, default to 1.
        // Unsure if this is good for the future, but I also chose to have
        // 1 as the default instance in the DB layout, rather than say, null for
        // when there are not multiple instances.
        if(!$this->has('instance_number')) {
            $this->merge([
                'instance_number'   =>  1,
            ]);
        }
        // Check to see if the user supplied one of our pre-defined point IDs for
        // this sighting.
        if(!$this->has('point_id')) {
            $point = $this->findClosestSpawnPoint($zone, floatval($this->x), floatval($this->y));
            $this->merge([
                'point_id'  =>  $point['point']->id,
                'point'     =>  $point['point']->toArray(),
                'distance'  =>  $point['distance'],
            ]);
        } else {
            // Pull in point details
            $this->merge([
                'point'     =>  SpawnPoint::where('id', $this->point_id)->first()->toArray(),
            ]);
        }

        // The javascript client posts a mob_index, but for ease of use, allow
        // API clients to simply specify the mob key from the NotoriousMonster table.
        if(!is_null($this->mob_id)) {
            $this->merge([
                'mob'   =>  Mob::where('id', $this->mob_id)->first()->toArray(),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'collaborator_password' =>  'required',
            'zone_id'               =>  'required|numeric',
            'instance_number'       =>  'numeric',
            'point_id'              =>  'numeric',
            'point'                 =>  'array',
            'x'                     =>  'required|numeric',
            'y'                     =>  'required|numeric',
            'mob_id'                =>  'numeric|nullable',
            'mob'                   =>  'array',
            'distance'              =>  'nullable|numeric',
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
