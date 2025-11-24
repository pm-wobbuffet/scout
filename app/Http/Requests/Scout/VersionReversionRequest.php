<?php

namespace App\Http\Requests\Scout;

use App\Models\Scout;
use App\Traits\VerifiesScoutUpdateRequests;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class VersionReversionRequest extends FormRequest
{
    use VerifiesScoutUpdateRequests;

    protected Scout $scout;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->scout = $this->route('scout');
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
            'version_number' => [
                'numeric',
                'min:1',
                'required',
                function (string $attribute, mixed $value, Closure $fail) {
                    // Need to determine if the version number passed is < the max current version #
                    $m = $this->scout->versions()->max('version');
                    if (\intval($value) >= $m) {
                        $fail("The target version number ({$value}) must be less than the current version # of the report ({$m})");
                    }
                }
            ],
            'update_user' => 'string|nullable',
        ];
    }
}
