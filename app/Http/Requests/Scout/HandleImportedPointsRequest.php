<?php

namespace App\Http\Requests\Scout;

use Illuminate\Foundation\Http\FormRequest;

class HandleImportedPointsRequest extends FormRequest
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
