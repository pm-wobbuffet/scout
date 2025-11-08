<?php

namespace App\Http\Requests\Api\V2;

use App\Traits\VerifiesScoutUpdateRequests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateScoutRequest extends FormRequest
{
    use VerifiesScoutUpdateRequests;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->verifyPermissions($this->route('scout'), $this);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'dead_mobs' => array_map(function ($mob) {
                if (!array_key_exists('instance_number', $mob)) {
                    $mob['instance_number'] = 1;
                }
                if (!array_key_exists('is_dead', $mob)) {
                    $mob['is_dead'] = 1;
                }
                return $mob;
            }, $this->input('dead_mobs', []))
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
            'collaborator_password' => 'string|required',
            'update_user'           => 'string|nullable',
            'sightings'             => 'array|nullable',
            'sightings.*.zone_id'   => 'numeric',
            'sightings.*.mob_id'    => 'numeric',
            'sightings.*.bnpcbase'  => 'numeric',
            'sightings.*.x'         => 'numeric',
            'sightings.*.y'         => 'numeric',
            /**
             * The person who reported this point. If null/blank, it will also look
             * for the value of update_user in the request and apply it here.
             */
            'sightings.*.reporter'  => 'string|nullable',
            'dead_mobs'             => 'array|nullable',
            'dead_mobs.*.mob_id'    => 'numeric',
            /**
             * If omitted, will default to instance 1
             */
            'dead_mobs.*.instance_number' => 'numeric|min:1',
            /**
             * If omitted, will assume marking mob as dead. If value is 0, will unmark a dead mob
             */
            'dead_mobs.*.is_dead'   => 'boolean',
        ];
    }
}
