<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            /**
             * The array of localized names for this zone, currently limited to those included in the Global client
             * @var array{'en': string, 'fr': string, 'de': string, 'ja': string}
             */
            'names'                 => $this->names,
            'map_id'                => $this->map_id,
            'default_instances'     => $this->default_instances,
            'expansion_id'          => $this->expansion_id,
            /**
             * Whether this zone will accept points that are not in the spawn point list
             * @var bool
             */
            'allow_custom_points'   => $this->allow_custom_points,
            'default_sort_priority' => $this->sort_priority,
            /**
             * The number of active spawn points attached to this zone
             */
            'spawn_point_count'     => $this->whenCounted('spawn_points'),
            /**
             * The number of active A rank mobs attached to this zone
             */
            'mobs_count'            => $this->whenCounted('mobs'),
            /**
             * The number of aetherytes active in this zone
             */
            'aetherytes_count'      => $this->whenCounted('aetherytes'),
            'aetherytes'            => AetheryteResource::collection($this->whenLoaded('aetherytes')),
            /**
             * The list of spawn points for this zone. Does not include any custom spawn points added on a per scout report basis.
             * @var array{'id': int, 'x': float, 'y': float}[]
             */
            'spawn_points'          => $this->whenLoaded('spawn_points', function ($points) {
                return $points->map(function ($point) {
                    return [
                        'id'    => $point->id,
                        'x'     => $point->x,
                        'y'     => $point->y,
                    ];
                });
            }),
        ];
    }
}
