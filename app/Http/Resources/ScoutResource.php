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
        //dd($this->resource);
        //dd($request);
        return [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'slug'                  => $this->slug,
            'collaborator_password' => $this->when($request->route('password') == $this->collaborator_password, $this->collaborator_password),
            'dead_mobs'             => ScoutDeadMobResource::collection($this->whenLoaded('dead_mobs')),
            'instance_data'         => ScoutZoneInstanceCountResource::collection($this->whenLoaded('instances')),
            'points'                => ScoutPointResource::collection($this->whenLoaded('points')),
            'custom_points'         => ScoutCustomPointResource::collection($this->whenLoaded('custom_points')),
            'scouts'                => $this->whenLoaded('scouts'),
            'finalized_at'          => $this->finalized_at,
        ];
    }
}
