<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScoutMetaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Did they submit a blank title? If so null it out
        if($this->has('title')) {
            if($this->title === '') {
                $this->merge([
                    'title' =>  null,
                ]);
            } else {
                $this->merge([
                    'title' => strip_tags($this->title),
                ]);
            }
        }
        $scouts = $this->scouts;
        array_walk_recursive($scouts, function(&$scouts) {
            $scouts = strip_tags($scouts);
        });
        $this->merge([
            'scouts'    =>  $scouts,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'                         =>  'string|nullable',
            'scouts'                        =>  'array',
            'scouts.*'                      =>  'string',
        ];
    }
}
