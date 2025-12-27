<?php

namespace App\Http\Requests\Scout;

use App\Models\Scout;
use App\Models\ScoutCustomPoint;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Validate and transform clipboard imported points
 * @property Scout $scout
 * @property array $point_data
 */
class HandleImportedPointsRequest extends FormRequest
{

    protected function prepareForValidation(): void
    {
        // Check to see if custom points were submitted. If so, we need to see if they already exist in the database
        $pd = [];
        foreach ($this->point_data as $point) {
            if ($point['point_type'] === 'custom_spawn_point') {
                if ($point['point_id'] < 0) {
                    // The point has only been created locally, we need to persist it to the DB and substitute in its
                    // valid > 0 value.
                    $pt = ScoutCustomPoint::firstOrCreate([
                        'scout_id'  => $this->scout->id,
                        'zone_id'   => $point['zone_id'],
                        'x'         => $point['x'],
                        'y'         => $point['y'],
                    ], [
                        'internal_id'           => $point['point_id'],
                        'assigned_by_import'    => $point['assigned_by_import'],
                    ]);
                    $point['point_id'] = $pt->id;
                }
            }
            $pd[] = $point;
        }
        $this->point_data = $pd;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'zonelist'                          => 'array',
            'zonelist.*'                        => 'string',
            'point_data'                        => 'array',
            'point_data.*.point_type'           => 'string|in:spawn_point,custom_spawn_point',
            'point_data.*.zone_id'              => 'integer|required',
            'point_data.*.mob_id'               => 'integer|nullable',
            'point_data.*.instance_number'      => 'integer|nullable',
            'point_data.*.point_id'             => 'required|integer',
            'point_data.*.x'                    => 'numeric|nullable',
            'point_data.*.y'                    => 'numeric|nullable',
            'point_data.*.assigned_by_import'   => 'boolean|nullable',
            'custom_points'                     => 'array',
        ];
    }
}
