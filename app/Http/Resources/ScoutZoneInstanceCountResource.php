<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Creates a special view of the Zone Resource that only includes the zone id and its instance count
 * without extraneous information
 */
class ScoutZoneInstanceCountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'zone_id'           => $this->id,
            'instance_count'    => $this->pivot->instance_count,
        ];
    }
}
