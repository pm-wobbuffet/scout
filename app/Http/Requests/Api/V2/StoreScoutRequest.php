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
        return true;
    }

    protected function prepareForValidation(): void {}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'     => 'nullable|string',
            'scouts'    => 'array',
            'scouts.*'  => 'string',
            'dead_mobs' => 'array|sometimes',
            'dead_mobs.*.mob_id' => 'numeric|required',
            /**
             * If not specified, this will default to instance 1
             */
            'dead_mobs.*.instance_number' => 'numeric|required',
            'custom_points' => 'array|sometimes',
            /**
             * Custom points should ALWAYS pass a negative number as their initial ID.
             * This will be stored in the internal_id field of the finished Scout Report
             * and can be used to map client reported custom points with the final Scout Report points.
             * The negative number was chosen to prevent clashes with actual database spawn point IDs.
             */
            'custom_points.*.id' => 'numeric|max:-1|required',
            'custom_points.*.zone_id' => 'numeric|required',
            'custom_points.*.point_type' => ['required', Rule::enum(SpawnPointTypeEnum::class)],
            'custom_points.*.x' => 'numeric|required|min:0',
            'custom_points.*.y' => 'numeric|required|min:0',
            /**
             * Array keys must be valid zone ID numbers
             * Any zone not specified will default to an instance count of 1
             * @var array<int<134,max>, bool>
             */
            'instance_data' => ['array', 'required', new ArrayKeysAreNumericRule],
            'instance_data.*' => 'numeric|min:1',


        ];
    }
}
