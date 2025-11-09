<?php

namespace App\Http\Requests\Api\V2;

use App\Models\Scout;
use App\Models\ScoutPoint;
use App\Traits\VerifiesScoutUpdateRequests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateScoutRequest extends FormRequest
{
    use VerifiesScoutUpdateRequests;

    protected $current_mobs;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->verifyPermissions($this->route('scout'), $this);
    }


    protected function prepareForValidation(): void
    {
        if ($this->has('dead_mobs')) {
            $this->getCurrentMobs($this->route('scout'));
        }
        // Make sure every mob has a status and that we don't allow marking assigned mobs as dead
        $this->merge([
            'dead_mobs' => array_map(function ($mob) {
                if (!array_key_exists('instance_number', $mob)) {
                    $mob['instance_number'] = 1;
                }
                if (!array_key_exists('is_dead', $mob)) {
                    $mob['is_dead'] = 1;
                }
                // Make sure the mob wasn't assigned already to a point
                if (
                    $mob['is_dead']
                    && in_array("{$mob['mob_id']}-{$mob['instance_number']}", $this->current_mobs)
                ) {
                    return;
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
            'title'                 => 'string',
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

    /**
     * Populate the list of currently assigned mobs for this zone
     * @param \App\Models\Scout $scout
     * @return void
     */
    private function getCurrentMobs(Scout $scout)
    {
        if (!$scout->relationLoaded('points')) {
            $scout->load('points');
        }
        foreach ($scout->points as $point) {
            $this->current_mobs[] = "{$point->mob_id}-{$point->instance_number}";
        }
    }
}
