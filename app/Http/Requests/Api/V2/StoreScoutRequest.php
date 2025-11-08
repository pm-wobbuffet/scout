<?php

namespace App\Http\Requests\Api\V2;

use App\Enums\SpawnPointTypeEnum;
use App\Rules\ArrayKeysAreNumericRule;
use App\Traits\UpdatesScoutReports;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Zone;

class StoreScoutRequest extends FormRequest
{
    use UpdatesScoutReports;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // @todo rate limiting?
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Allow them to just pass an array of scout names if they want
        if (isset($this->scouts) && is_array($this->scouts)) {
            $scouts = [];
            foreach ($this->scouts as $key => $value) {
                $scouts[] = [
                    'scout_name' => $value,
                ];
            }
            $this->merge([
                'scouts' => $scouts,
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
            'title'                             => 'nullable|string',
            /**
             * You may also pass a simple array of strings representing scout names
             * and they will be transformed into the proper format
             */
            'scouts'                            => 'array',
            'scouts.*.scout_name'               => 'string|required',
            'dead_mobs'                         => 'array|sometimes',
            'dead_mobs.*.mob_id'                => 'numeric|required',
            'dead_mobs.*.instance_number'       => 'numeric|required',
            'custom_points'                     => 'array|sometimes',
            /**
             * Custom points should ALWAYS pass a negative number as their initial ID.
             * This will be stored in the internal_id field of the finished Scout Report
             * and can be used to map client reported custom points with the final Scout Report points.
             * The negative number was chosen to prevent clashes with actual database spawn point IDs.
             */
            'custom_points.*.id'                => 'numeric|max:-1|required',
            'custom_points.*.zone_id'           => 'numeric|required',
            'custom_points.*.point_type'        => ['required', Rule::enum(SpawnPointTypeEnum::class)],
            'custom_points.*.x'                 => 'numeric|required|min:0',
            'custom_points.*.y'                 => 'numeric|required|min:0',
            /**
             * Any zone not specified will default to an instance count of 1
             */
            'instance_data'                     => 'array',
            'instance_data.*.zone_id'           => 'numeric',
            'instance_data.*.instance_count'    => 'numeric|min:1',

            'point_data'                        => 'array',
            'point_data.*.point_id'             => 'numeric|required|min:1',
            'point_data.*.zone_id'              => 'numeric|required',
            'point_data.*.point_type'           => ['required', Rule::enum(SpawnPointTypeEnum::class)],
            /**
             * Pass null to mark the point as occupied by a B or S rank mob
             */
            'point_data.*.mob_id'               => 'required|nullable',
            'point_data.*.instance_number'      => 'numeric|min:1|nullable',
            /**
             * Display name of the person assigning this point
             */
            'point_data.*.reporter'             => 'string|nullable',
            /**
             * Can pass the exact X/Y values that the mob was seen at. Intended to be used
             * in the Mark Summary section or exporting to other tools
             */
            'point_data.*.x'                    => 'numeric|nullable',
            /**
             * Can pass the exact X/Y values that the mob was seen at. Intended to be used
             * in the Mark Summary section or exporting to other tools
             */
            'point_data.*.y'                    => 'numeric|nullable',

        ];
    }
}
