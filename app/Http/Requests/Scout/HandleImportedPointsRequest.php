<?php

namespace App\Http\Requests\Scout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class HandleImportedPointsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Check to see if custom points were submitted. If so, we need to see if they already exist in the database
        foreach ($this->point_data as $point) {
            if ($point->point_type === 'custom_spawn_point') {
                if ($point->point_id < 0) {
                    // The point has only been created locally, we need to persist it to the DB and substitute in its
                    // valid > 0 value.
                    $this->scout->custom_points()->where('zone_id', $point->zone_id)
                        ->where('x', $point->x)
                        ->where('y', $point->y);
                }
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
            'zonelist' => 'array',
            'zonelist.*' => 'string',
            'point_data' => 'array',
            'point_data.*.point_type' => 'string|in:spawn_point,custom_spawn_point',
            'point_data.*.zone_id'  => 'integer|required',
            'point_data.*.mob_id'   => 'integer|nullable',
            'point_data.*.instance_number' => 'integer|nullable',
            'point_data.*.point_id' => 'required|integer',
            'point_data.*.x' => 'numeric|nullable',
            'point_data.*.y' => 'numeric|nullable',
        ];
    }
}
