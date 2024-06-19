<?php

namespace App\Http\Requests;

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

    protected function passedValidation(): void 
    {
        $this->merge([
            'collaborator_password' =>  str(bin2hex(random_bytes(4))),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Validate input
        // Make sure they're only submitting numerics so they can't do silly things
        // if they manually mess with any inputs in JS
        return [
            'point_data'                    =>  'required|array',
            'instance_data'                 =>  'array',
            'instance_data.*'               =>  'integer',
            'point_data.*.*.*.point_id'     =>  'numeric',
            'point_data.*.*.*.mob_id'       =>  'numeric',
            'point_data.*.*.*.x'            =>  'numeric',
            'point_data.*.*.*.y'            =>  'numeric',
            'point_data.*.*.*.expansion_id' =>  'integer',
        ];
    }
}
