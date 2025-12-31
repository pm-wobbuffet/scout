<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomPointViewRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        if (!$this->has('rounding')) {
            $this->merge([
                'rounding' => 0.1
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
            'rounding' => [
                'nullable',
                'numeric',
                Rule::in(['0.1', '0.5', '1', '2']),
            ],
        ];
    }
}
