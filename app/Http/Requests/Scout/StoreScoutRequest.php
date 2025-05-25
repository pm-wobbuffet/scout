<?php

namespace App\Http\Requests\Scout;

use Illuminate\Foundation\Http\FormRequest;

class StoreScoutRequest extends FormRequest
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
        // TODO: make sure these fields are defined, array substructures checked
        return [
            'points'    => 'array|nullable',
            'dead_mobs' => 'array|nullable',
            'instance_data' => 'array|nullable',
        ];
    }
}
