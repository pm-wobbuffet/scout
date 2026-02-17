<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
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
            'readonly_url'      => route('scout.view', $this),
            'collaborate_url'   => $this->when(
                $request->input('collaborator_password') == $this->collaborator_password,
                route('scout.view', [$this, $this->collaborator_password])
            ),
            'title'             => $this->title,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            /**
             * The time the report was finalized. If not null, no further changes the scouting report may be submitted.
             */
            'finalized_at' => $this->finalized_at,
            /**
             * Represents the status of given spawn points within the scouting report
             * including occupancy status.
             */
            'points' => ScoutPointResource::collection($this->whenLoaded('points')),
            /**
             * The collection of CustomPoints for the scouting report. internal_id is the originally submitted client generated ID
             */
            'custom_points' => $this->whenLoaded('custom_points'),
            /**
             * The names of all scouts (reporters) who contributed to the scout report
             * @var string[]
             */
            'scouts' => $this->whenLoaded('scouts', function ($scout_list) {
                return $scout_list->pluck('scout_name');
            }),
            /**
             * An array of mobs marked as dead at the time of scouting.
             * @var array{'mob_id': int, 'instance_number': int}
             */
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
