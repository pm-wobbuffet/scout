<?php

namespace App\Http\Requests\Scout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOccupiedPointRequest extends FormRequest
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
            'point_type'        => ['string', Rule::in(['spawn_point', 'custom_spawn_point'])],
            'point_id'          => 'numeric|required',
            'zone_id'           => 'numeric|required',
            'instance_number'   => 'numeric|required',
            'mob_id'            => 'numeric|sometimes|nullable',
        ];
    }
}
