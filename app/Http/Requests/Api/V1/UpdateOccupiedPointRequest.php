<?php

namespace App\Http\Requests\Api\V1;

use App\Models\SpawnPoint;
use App\Models\Zone;
use App\Traits\CalculatesNearestPoint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UpdateOccupiedPointRequest extends FormRequest
{
    use CalculatesNearestPoint;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $scout = $this->route('scout');
        if (!$scout) {
            Log::debug("No Scouting report was found for this request, {req}", ['req' => $this]);
            return false;
        }
        // Check to make sure they supplied the correct collaborator_password
        // to prevent unauthorized users from supplying updates
        if ($scout->collaborator_password !== $this->collaborator_password) {
            Log::debug("An invalid collaborator password was submitted for this request, {req}", ['req' => $this]);
            return false;
        }

        // Make sure we don't make any changes to an existing map that's finalized
        if (!is_null($scout->finalized_at)) {
            Log::debug("An API request was made to update a finalized scouting report, {req}", ['req' => $this]);
            return false;
        }

        return true;
    }

    /**
     * Do any necessary transforms on incoming user data to make a proper final request
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Grab a reference to the zone
        if ($this->has('zone_id')) {
            $zone = Zone::query()
                ->with(['spawn_points'])
                ->where('id', $this->zone_id)->firstOrFail();
        } else {
            $zone = null;
        }

        // If no instance number is specified, default to 1.
        // Unsure if this is good for the future, but I also chose to have
        // 1 as the default instance in the DB layout, rather than say, null for
        // when there are not multiple instances.
        if (!$this->has('instance_number')) {
            $this->merge([
                'instance_number'   =>  1,
            ]);
        }
        // Check to see if the user supplied one of our pre-defined point IDs for
        // this sighting.
        if (!$this->has('point_id') && is_null($zone)) {
            return;
        }
        if (!$this->has('point_id')) {
            $point = $this->findClosestSpawnPoint(
                $this->getSpawnPointsForZone($zone, $this->route('scout')),
                floatval($this->x),
                floatval($this->y)
            );
            $this->merge([
                'point_id'  =>  $point['point']->id,
                'point'     =>  $point['point'],
                'distance'  =>  $point['distance'],
            ]);
        } else {
            // Pull in point details
            $this->merge([
                'point'     =>  SpawnPoint::where('id', $this->point_id)->first()->toArray(),
                'distance'  =>  0,
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
            'point_id'              =>  [
                'numeric',
                Rule::requiredIf(!$this->has('zone_id'))
            ],
            'zone_id'               =>  [
                'numeric',
                Rule::requiredIf(!$this->has('point_id'))
            ],
            'instance_number'       =>  'numeric',
            'x'                     =>  'numeric|required_with:zone_id',
            'y'                     =>  'numeric|required_with:zone_id',
            'status'                =>  'numeric|required',
        ];
    }
}
