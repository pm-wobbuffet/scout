<?php

namespace App\Http\Requests;

use App\Models\Zone;
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

    protected function prepareForValidation(): void
    {
        // If a request includes no instance_data, default to including
        // whatever we have in the database presently
        if(!$this->has('instance_data')) {
            $this->merge([
                'instance_data' => $this->getDefaultInstanceCounts(),
            ]);
        }
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
            'point_data'                    =>  'array',
            'instance_data'                 =>  'array',
            'instance_data.*'               =>  'integer|nullable',
            'point_data.*.*.*.point_id'     =>  'numeric|nullable',
            'point_data.*.*.*.mob_id'       =>  'numeric|nullable',
            'point_data.*.*.*.x'            =>  'numeric|nullable',
            'point_data.*.*.*.y'            =>  'numeric|nullable',
            'point_data.*.*.*.valid_mobs'   =>  'array|nullable',
            'point_data.*.*.*.expansion_id' =>  'integer|nullable',
            'point_data.*'                  =>  'array',
            'point_data.*.*'                =>  'array',
            'custom_points'                 =>  'array',
            'collaborator_password'         =>  'required',
        ];
    }

    private function getDefaultInstanceCounts() {
        return Zone::query()
        ->select(['id', 'default_instances'])
        ->orderBy('sort_priority')
        ->get()
        ->pluck('default_instances', 'id')->toArray();
    }
}
