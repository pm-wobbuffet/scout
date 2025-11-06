<?php

namespace App\Http\Resources\Api\V2;

use App\Models\SpawnPoint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpawnPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'zone_id'       => $this->zone_id,
            /**
             * @var float
             */
            'x'             => $this->x,
            /**
             * @var float
             */
            'y'             => $this->y,
            /**
             * The internal type of this point. Valid strings are 'spawn_point' and 'custom_spawn_point'
             * @var string
             */
            'point_type'    => $this->point_type,
            /**
             * @var int[]
             * List of valid Mob IDs for this spawn point
             */
            'valid_mobs'    => $this->whenLoaded('valid_mobs', function ($mobs) {
                return $mobs->pluck('mob_id');
            }),
        ];
    }
}
