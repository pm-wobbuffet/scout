<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScoutCustomPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        return [
            'id'    => $this->id,
            'point_type' => 'custom_spawn_point',
            'scout_id' => $this->scout_id,
            'zone_id' => $this->zone_id,
            'x' => $this->x,
            'y' => $this->y,
            'internal_id' => $this->internal_id,
            'valid_mobs' => $this->zone->mobs,
        ];
    }
}
