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
            'instance_data.*'               =>  'integer',
            'point_data.*.*.*.point_id'     =>  'numeric',
            'point_data.*.*.*.mob_id'       =>  'numeric',
            'point_data.*.*.*.x'            =>  'numeric',
            'point_data.*.*.*.y'            =>  'numeric',
            'point_data.*.*.*.expansion_id' =>  'integer',
            'point'                         =>  'array',
            'mob'                           =>  'required',
            'instance_number'               =>  'integer',
            'zone_id'                       =>  'integer',
        ];
    }
}
