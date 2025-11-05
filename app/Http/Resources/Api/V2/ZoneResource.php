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
            'names'                 => $this->names,
            'map_id'                => $this->map_id,
            'default_instances'     => $this->default_instances,
            'expansion_id'          => $this->expansion_id,
            'allow_custom_points'   => $this->allow_custom_points,
            'default_sort_priority' => $this->sort_priority,
            'spawn_point_count'     => $this->whenCounted('spawn_points'),
            'mobs_count'            => $this->whenCounted('mobs'),
            'aetherytes_count'      => $this->whenCounted('aetherytes'),
            'aetherytes'            => AetheryteResource::collection($this->whenLoaded('aetherytes')),
        ];
    }
}
