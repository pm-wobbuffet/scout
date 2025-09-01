<?php

namespace App\Http\Requests\Scout;

use App\Models\SpawnPoint;
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
        ];
    }
}
