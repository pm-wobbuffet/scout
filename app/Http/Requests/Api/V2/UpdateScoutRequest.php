<?php

namespace App\Http\Requests\Api\V2;

use App\Enums\SpawnPointTypeEnum;
use App\Models\Scout;
use App\Models\ScoutPoint;
use App\Models\Zone;
use App\Traits\CalculatesNearestPoint;
use App\Traits\VerifiesScoutUpdateRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScoutRequest extends FormRequest
{
    use VerifiesScoutUpdateRequests, CalculatesNearestPoint;

    protected array $current_mobs;
    protected array $bnpcbase_map;
    protected array $mob_index_map;
    protected array $used_zones;

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
        if ($this->has('sightings')) {
            $this->getMobMaps(array_reduce($this->sightings ?? [], function ($carry, $val) {
                if ($val['zone_id'] && $val['zone_id'] !== null && !in_array($val['zone_id'], $carry)) {
                    $carry[] = $val['zone_id'];
                    return $carry;
                }
            }, []));
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
        // Normalize any sighting's data
        $this->merge([
            'sightings' => array_map(function ($sighting) {
                if (!array_key_exists('instance_number', $sighting)) {
                    $sighting['instance_number'] = 1;
                }
                if (!array_key_exists('mob_id', $sighting)) {
                    if (array_key_exists('bnpcbase', $sighting)) {
                        $sighting['mob_id'] = $this->bnpcbase_map[$sighting['bnpcbase']] ?? null;
                    } elseif (array_key_exists('mob_index', $sighting)) {
                        $sighting['mob_id'] = $this->mob_index_map[$sighting['zone_id']][$sighting['mob_index']] ?? null;
                    }
                }
                if (!array_key_exists('point_id', $sighting)) {
                    // Need to figure out closest point
                    $pt = $this->findClosestSpawnPoint(
                        $this->getSpawnPointsForZone($this->used_zones[$sighting['zone_id']], $this->route('scout')),
                        floatval($sighting['x']),
                        floatval($sighting['y'])
                    );
                    if ($pt['distance'] < 2) {
                        $sighting['point_id'] = $pt['point']->id;
                        $sighting['point_type'] = $pt['point']->point_type;
                    } else {
                        $z = $this->used_zones[$sighting['zone_id']];
                        if ($z->allow_custom_points) {
                            $newpt = $this->addCustomPoint(
                                $this->route('scout'),
                                $z,
                                floatval($sighting['x']),
                                floatval($sighting['y'])
                            );
                            $sighting['point_type'] = 'custom_spawn_point';
                            $sighting['point_id'] = $newpt->id;
                        }
                    }
                }
                unset($sighting['bnpcbase'], $sighting['mob_index']);
                return $sighting;
            }, $this->input('sightings', []))
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
            'sightings.*.zone_id'   => 'numeric|required',
            /**
             * You may pass either mob_id, mob_index, or bnpcbase key. The latter 2 will be coerced into a mob_id
             */
            'sightings.*.mob_id'    => 'numeric|required_without_all:sightings.*.mob_index,sightings.*.bnpcbase',
            'sightings.*.mob_index' => 'numeric|required_without_all:sightings.*.mob_id,sightings.*.bnpcbase',
            'sightings.*.bnpcbase'  => 'numeric|required_without_all:sightings.*.mob_id,sightings.*.mob_index',
            /**
             * You may manually pass a point_id from the database (if known). It is preferable to send only an X and Y however.
             */
            'sightings.*.point_id'  => 'numeric',
            'sightings.*.point_type' => Rule::enum(SpawnPointTypeEnum::class),
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

    private function addCustomPoint(Scout $scout, Zone $zone, float $x, float $y)
    {
        return $scout->custom_points()->create([
            'zone_id' => $zone->id,
            'x' => $x,
            'y' => $y,
        ]);
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

    /**
     * Populate the bnpc and mob index maps used for filling in missing Sighting data
     * @param int[] $zone_list
     * @return void
     */
    private function getMobMaps(array $zone_list): void
    {
        $z = Zone::query()
            ->select(['id', 'name', 'allow_custom_points'])
            ->whereIn('id', $zone_list)
            ->with('mobs')
            ->get();
        foreach ($z as $zone) {
            foreach ($zone->mobs as $mob) {
                $this->bnpcbase_map[$mob->bNpcBase] = $mob->id;
                $this->mob_index_map[$zone->id][$mob->mob_index] = $mob->id;
                $this->used_zones[$zone->id] = $zone;
            }
        }
    }
}
