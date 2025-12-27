<?php

namespace App\Http\Requests\Scout;

use Illuminate\Foundation\Http\FormRequest;

class StoreScoutRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        // Note points that were imported via chat logs for usage in map displays
        $by_import = [];
        foreach ($this->points as $point) {
            if ($point['assigned_by_import'] && $point['point_id'] < 0) {
                $by_import[] = $point['point_id'];
            }
        }
        $customs = $this->custom_points;
        foreach ($customs as &$point) {
            if (in_array($point['id'], $by_import)) {
                $point['assigned_by_import'] = 1;
            }
        }
        $this->merge(['custom_points' => $customs]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'custom_points'                 => 'array|nullable',
            'custom_points.*.id'            => 'numeric|required',
            'custom_points.*.x'             => 'numeric|required',
            'custom_points.*.y'             => 'numeric|required',
            'custom_points.*.zone_id'       => 'numeric|required',
            'custom_points.*.scout_id'      => 'nullable|numeric',
            'custom_points.*.valid_mobs'    => 'nullable|array',
            'custom_points.*.point_type'    => 'required|string',
            'points'                        => 'array|nullable',
            'points.*.point_type'           => 'required|string',
            'points.*.point_id'             => 'required|numeric',
            'points.*.instance_number'      => 'required|numeric',
            'points.*.x'                    => 'nullable|numeric',
            'points.*.y'                    => 'nullable|numeric',
            'points.*.zone_id'              => 'required|numeric',
            'points.*.mob_id'               => 'nullable|numeric',
            'points.*.reporter'             => 'nullable|string',
            'points.*.assigned_by_import'   => 'boolean|nullable',
            'dead_mobs'                     => 'array|nullable',
            'dead_mobs.*.mob_id'            => 'required|numeric',
            'dead_mobs.*.instance_number'   => 'numeric',
            'instance_data'                 => 'array|nullable',
            'instance_data.*.zone_id'       => 'numeric',
            'instance_data.*.instance_count' => 'numeric|min:1',
            'scouts'                        => 'array|nullable',
            'scouts.*.scout_name'           => 'string',
        ];
    }
}
