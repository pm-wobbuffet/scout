<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScoutPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'zone_id'           => $this->zone_id,
            'mob_id'            => $this->mob_id,
            'instance_number'   => $this->instance_number,
            'point_type'        => $this->point_type,
            'point_id'          => $this->point_id,
            'x'                 => $this->x,
            'y'                 => $this->y,
            'reporter'          => $this->reporter,
        ];
    }
}
