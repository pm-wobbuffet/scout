<?php

namespace App\Http\Requests\Scout;

use App\Models\SpawnPoint;
use App\Models\ScoutCustomPoint;
use Illuminate\Foundation\Http\FormRequest;

class AssignMobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        // If only a point_id was submitted, we can pluck the zone from it if needed
        if (!$this->has('zone_id') && $this->has('point_id') && $this->has('point_type')) {
            if ($this->input('point_type') == 'spawn_point') {
                $point = SpawnPoint::where('id', $this->input('point_id'))->first();
                $this->merge([
                    'point_id' => $point->id,
                ]);
            }
        }
        // Check to see if a custom spawn point was submitted that hasn't been created in the DB yet
        if ($this->input('point_type') == 'custom_spawn_point') {
            if (intval($this->input('point_id')) < 0) {
                // Submitted a custom point that needs a new DB ID assigned
                $point = ScoutCustomPoint::create([
                    'scout_id' => $this->scout->id,
                    'zone_id' => $this->input('zone_id'),
                    'x' => $this->input('point.x'),
                    'y' => $this->input('point.y'),
                    'internal_id' => $this->input('point_id'),
                ]);
                $this->merge([
                    'point_id' => $point->id,
                ]);
            }
        }

        if (!$this->has('instance_number')) {
            $this->merge([
                'instance_number' => 1,
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
            'collaborator_password' => 'string|required',
            'instance_number'       => 'numeric',
            'mob_id'                => 'numeric|nullable',
            'point_type'            => 'string|required',
            'point_id'              => 'numeric|required',
            'zone_id'               => 'numeric',
            'reporter'              => 'string|nullable',
            'point'                 => 'array|nullable',
        ];
    }
}
