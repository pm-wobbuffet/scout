<?php

namespace App\Http\Requests\Api\V2;

use App\Models\Scout;
use App\Models\ScoutPoint;
use App\Traits\VerifiesScoutUpdateRequests;
use Illuminate\Foundation\Http\FormRequest;

class DeleteScoutPointRequest extends FormRequest
{
    use VerifiesScoutUpdateRequests;
    protected Scout $scout;
    protected ScoutPoint $point;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->scout = $this->route('scout');
        $this->point = $this->route('point');
        if ($this->point->scout_id !== $this->scout->id) return false;

        return $this->verifyPermissions($this->scout, $this);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'collaborator_password' => 'string|required',
        ];
    }
}
