<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
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
        return [
            'slug' => $this->slug,
            'collaborator_password' => $this->when(
                $request->input('collaborator_password') == $this->collaborator_password,
                $this->collaborator_password
            ),
            'title' => $this->title,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'finalized_at' => $this->finalized_at,
            'points' => $this->whenLoaded('points'),
            'custom_points' => $this->whenLoaded('custom_points'),
            'scouts' => $this->whenLoaded('scouts', function ($scout_list) {
                return $scout_list->pluck('scout_name');
            }),
            'dead_mobs' => $this->whenLoaded('dead_mobs', function ($dead_mobs) {
                return $dead_mobs->map(function ($mob) {
                    return [
                        'mob_id' => $mob->mob_id,
                        'instance_number' => $mob->instance_number,
                    ];
                });
            }),
        ];
    }
}
