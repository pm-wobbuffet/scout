<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\ScoutZoneInstanceCountResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ScoutResource extends JsonResource
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
            'id'                    => $this->id,
            'title'                 => $this->title,
            'slug'                  => $this->slug,
            'collaborator_password' => $this->when(in_array('collaborator_password', $this->getVisible()),$this->collaborator_password),
            'dead_mobs'             => ScoutDeadMobResource::collection($this->whenLoaded('dead_mobs')),
            'instance_data'         => ScoutZoneInstanceCountResource::collection($this->whenLoaded('instances')),
            'points'                => ScoutPointResource::collection($this->whenLoaded('points')),
        ];
    }
}
