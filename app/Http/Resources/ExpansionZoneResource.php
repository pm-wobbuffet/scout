<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpansionZoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'expansion_id'          => $this->expansion_id,
            'map_id'                => $this->map_id,
            'max_coord_size'        => $this->max_coord_size,
            'name'                  => $this->name,
            'names'                 => $this->names,
            'size_factor'           => $this->size_factor,
            'sort_priority'         => $this->sort_priority,
            'allow_custom_points'   => $this->allow_custom_points,
            'default_instances'     => $this->default_instances,
            'aetherytes'            => $this->whenLoaded('aetherytes'),
            'mobs'                  => $this->whenLoaded('mobs'),
            'spawn_points'          => $this->whenLoaded('spawn_points'),
        ];
    }
}
