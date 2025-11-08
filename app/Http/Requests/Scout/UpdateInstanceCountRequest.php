<?php

namespace App\Http\Requests\Scout;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstanceCountRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'instance_data'                     => 'required|array',
            'instance_data.*.zone_id'           => 'numeric|min:1',
            'instance_data.*.instance_count'    => 'numeric',
        ];
    }
}
