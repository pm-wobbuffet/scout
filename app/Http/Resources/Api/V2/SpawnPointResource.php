<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpawnPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'zone_id'       => $this->zone_id,
            'x'             => $this->x,
            'y'             => $this->y,
            'point_type'    => $this->point_type,
            // Only need the mob_ids for the API calls. They can ping the /mobs endpoints for info
            'valid_mobs'    => $this->whenLoaded('valid_mobs')->pluck('mob_id'),
        ];
    }
}
