<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A compact version of a scouting report and the details needed to bring it back
 * from the history in a Revert operation
 * 
 * @mixin \App\Models\Scout
 */
class ScoutVersionCompactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'instances' => $this->whenLoaded('instances', function ($instances) {
                return $instances->map(function ($instance) {
                    return [
                        'zone_id' => $instance->pivot->zone_id,
                        'instance_count' => $instance->pivot->instance_count,
                    ];
                });
            }),
            'scouts'    => $this->whenLoaded('scouts', function ($scouts) {
                return $scouts->map(function ($scouter) {
                    return [
                        'id'            => $scouter->id,
                        'scout_name'    => $scouter->scout_name,
                    ];
                });
            }),
            'dead_mobs' => $this->whenLoaded('dead_mobs', function ($dead_mobs) {
                return $dead_mobs->map(function ($mob) {
                    return [
                        'mob_id'            => $mob->mob_id,
                        'instance_number'   => $mob->instance_number,
                    ];
                });
            }),
            'custom_points' => $this->whenLoaded('custom_points', function ($custom_points) {
                return $custom_points->map(function ($point) {
                    return [
                        'id'            => $point->id,
                        'zone_id'       => $point->zone_id,
                        'x'             => $point->x,
                        'y'             => $point->y,
                        'internal_id'   => $point->internal_id,
                    ];
                });
            }),
            'points' => $this->whenLoaded('points', function ($points) {
                return $points->map(function ($point) {
                    return [
                        'id'            => $point->id,
                        'zone_id'       => $point->zone_id,
                        'point_type'    => $point->point_type,
                        'point_id'      => $point->point_id,
                        'mob_id'        => $point->mob_id,
                        'instance_number' => $point->instance_number,
                        'reporter'      => $point->reporter,
                        'x'             => $point->x,
                        'y'             => $point->y,
                        'internal_id'   => $point->internal_id,
                        'created_at'    => $point->created_at,
                        'updated_at'    => $point->updated_at,
                    ];
                });
            })
        ];
    }
}
