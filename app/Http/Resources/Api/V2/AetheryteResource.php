<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AetheryteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'zone_id'   => $this->zone_id,
            'x'         => $this->x,
            'y'         => $this->y,
            'name'      => $this->name,
            'names'     => $this->names,
            'ui_icon'   => $this->icon,
        ];
    }
}
