<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobResource extends JsonResource
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
            'zone_id'               => $this->zone_id,
            'bNpcBase'              => $this->bNpcBase,
            'mob_index'             => $this->mob_index,
            'spawn_points_count'    => $this->whenCounted('spawn_points'),
            'spawn_points'          => $this->whenLoaded('spawn_points')->map(function (mixed $item, int $key) {
                return [
                    'id'    => $item->id,
                    'x'     => $item->x,
                    'y'     => $item->y,
                ];
            }),

        ];
    }
}
