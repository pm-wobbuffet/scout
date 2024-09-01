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
        // If there's no custom point data, initialize as an empty array, just to make
        // API calls with more brevity
        if(!$this->has('custom_points')) {
            $this->merge([
                'custom_points' => [],
            ]);
        }

        // Generate the collaborator password automatically.
        $this->merge([
            'collaborator_password' =>  str(bin2hex(random_bytes(4))),
        ]);

        // Did they submit a blank title? If so null it out
        if(!$this->has('title')) {
            $this->merge(['title' => '']);
        }
        $this->merge([
            'title' => strip_tags($this->title)
        ]);

        $scouts = $this->scouts ?? [];
        if(is_array($scouts)) {
            array_walk_recursive($scouts, function(&$scouts) {
                $scouts = strip_tags($scouts);
            });
            $this->merge([
                'scouts'    =>  $scouts,
            ]);
        } else {
            $this->merge([
                'scouts'    =>  [],
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
            'title'                         =>  'string|nullable',
            'scouts'                        =>  'array',
            'scouts.*'                      =>  'string',
            'mob_status'                    =>  'array|nullable',
            'occupied_points'               =>  'array|nullable',
        ];
    }

    /**
     * Get the current number of default instances per zone
     *
     * @return array
     */
    private function getDefaultInstanceCounts(): array {
        return Zone::query()
        ->select(['id', 'default_instances'])
        ->orderBy('sort_priority')
        ->get()
        ->pluck('default_instances', 'id')->toArray();
    }
}
