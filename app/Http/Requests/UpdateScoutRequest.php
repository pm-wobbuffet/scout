<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScoutRequest extends FormRequest
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
            'point_data'                    =>  'required|array',
            'instance_data'                 =>  'array',
            'instance_data.*'               =>  'integer|nullable',
            'point_data.*.*.*.point_id'     =>  'numeric|nullable',
            'point_data.*.*.*.mob_id'       =>  'numeric|nullable',
            'point_data.*.*.*.x'            =>  'numeric|nullable',
            'point_data.*.*.*.y'            =>  'numeric|nullable',
            'point_data.*.*.*.expansion_id' =>  'integer|nullable',
            'point_data.*'                  =>  'array',
            'point_data.*.*'                =>  'array',
            'point'                         =>  'array',
            'mob'                           =>  'required',
            'instance_number'               =>  'integer',
            'zone_id'                       =>  'integer',
        ];
    }
}
