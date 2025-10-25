<?php

namespace App\Http\Requests\Scout;

use App\Models\ScoutCustomPoint;
use Illuminate\Foundation\Http\FormRequest;

class ClearPointRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        if (!$this->has('instance_number')) {
            $this->merge([
                'instance_number' => 1,
            ]);
        }
        // Check to see if a custom spawn point was submitted that hasn't been created in the DB yet
        if ($this->input('point_type') == 'custom_spawn_point') {
            if (intval($this->input('point_id')) < 0) {
                // Submitted a custom point that needs a new DB ID assigned if it doesn't exist
                $point = ScoutCustomPoint::firstOrCreate([
                    'scout_id' => $this->scout->id,
                    'zone_id' => $this->input('zone_id'),
                    'x' => $this->input('point.x'),
                    'y' => $this->input('point.y'),
                ], [
                    'internal_id' => $this->input('point_id'),
                ]);
                $this->merge([
                    'point_id' => $point->id,
                ]);
            }
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
            'point_type'        => 'required|string',
            'id'                => 'required|numeric',
            'instance_number'   => 'numeric',
            'zone_id'           => 'numeric',
            'internal_id'       => 'numeric',
        ];
    }
}
