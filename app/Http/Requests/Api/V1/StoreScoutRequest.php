<?php

namespace App\Http\Requests\Api\V1;

use App\Models\SpawnPoint;
use App\Models\Zone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreScoutRequest extends FormRequest
{

    protected function prepareForValidation(): void
    {
        // If a request includes no instance_data, default to including
        // whatever we have in the database presently
        if (!$this->has('instance_data')) {
            $this->merge([
                'instance_data' => $this->getDefaultInstanceCounts(),
            ]);
        }
        // If there's no custom point data, initialize as an empty array, just to make
        // API calls with more brevity
        if (!$this->has('custom_points')) {
            $this->merge([
                'custom_points' => [],
            ]);
        }
        // Did they submit a blank title? If so null it out
        if (!$this->has('title')) {
            $this->merge(['title' => '']);
        }
        $this->merge([
            'title' => strip_tags($this->title)
        ]);

        $scout_ar = [];
        $scouts = $this->scouts ?? [];
        foreach ($scouts as $scout) {
            $scout_ar[] = [
                'scout_name' => strip_tags($scout),
            ];
        }
        $this->merge(['scouts' => $scout_ar]);
    }

    protected function passedValidation(): void
    {
        // Convert from the old point format to the new one
        $point_data = [];
        foreach ($this->point_data ?? [] as $zone_id => $instance) {
            foreach ($instance as $instance_number => $mob_list) {
                foreach ($mob_list as $mob_sighting) {
                    $point_data[] = [
                        'mob_id'            => $mob_sighting['mob_id'],
                        'point_id'          => $mob_sighting['point_id'],
                        'zone_id'           => $zone_id,
                        'instance_number'   => $instance_number,
                        'point_type'        => intval($mob_sighting['point_id']) > 0 ? 'spawn_point' : 'custom_spawn_point',
                    ];
                }
            }
        }

        $instances = [];
        foreach ($this->instance_data as $zone_id => $instance_count) {
            if (intval($instance_count) > 1) {
                $instances[$zone_id] = intval($instance_count);
            }
        }
        // Look for occupied points and move them to the point_data array
        $p = SpawnPoint::select(['id', 'zone_id'])->get()->pluck('zone_id', 'id');
        foreach ($this->occupied_points ?? [] as $point_id => $instances) {
            foreach ($instances as $instance_number => $status) {
                if ($status) {
                    $point_data[] = [
                        'mob_id'    => null,
                        'point_id'  => $point_id,
                        'zone_id'   => $p[$point_id],
                        'instance_number' => $instance_number,
                        'point_type' => intval($mob_sighting['point_id']) > 0 ? 'spawn_point' : 'custom_spawn_point',
                    ];
                }
            }
        }

        // Replace keys with their new V2 appropriate transformed versions
        $this->replace([
            'point_data' => $point_data,
            'instance_data' => $instances,
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
            'title'                         =>  'string|nullable',
            'scouts'                        =>  'array|nullable',
            'scouts.*.scout_name'           =>  'string',
            'mob_status'                    =>  'array|nullable',
            'mob_status.*'                  =>  'array',
            'occupied_points'               =>  'array|nullable',
        ];
    }

    /**
     * Get the current number of default instances per zone
     * In V2, we only need the instances where the default is > 1
     * 
     * @return array
     */
    private function getDefaultInstanceCounts(): array
    {
        return Zone::query()
            ->select(['id', 'default_instances'])
            ->where('default_instances', '>', 1)
            ->orderBy('sort_priority')
            ->get()
            ->pluck('default_instances', 'id')->toArray();
    }
}
