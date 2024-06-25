<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportPointsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'instance_data'                             =>  'array',
            'instance_data.*'                           =>  'integer|nullable',
            'custom_points'                             =>  'array',
            'custom_points.*.id'                        =>  'required|numeric',
            'custom_points.*.mob_index'                 => 'string',
            'custom_points.*.zone_id'                   => 'numeric',
            'custom_points.*.valid_mobs'                => 'array',
            'custom_points.*.valid_mobs.*.id'           => 'numeric',
            'custom_points.*.valid_mobs.*.mob_index'    => 'numeric',
            'custom_points.*.valid_mobs.*.zone_id'      => 'numeric',

        ];
    }
}
