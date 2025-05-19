<?php

namespace App\Http\Requests\Scout;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMobStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        if(!$this->has('instance_number')) {
            $this->merge([
                'instance_number' => 1,
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
            'mob_id'            => 'required|numeric',
            'instance_number'   => 'required|numeric',
            'is_dead'           => 'required|boolean',
        ];
    }
}
