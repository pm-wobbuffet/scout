<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ScoutCustomPoint;
use App\Models\SpawnPoint;

class ScoutPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id'                => $this->id,
            'zone_id'           => $this->zone_id,
            'x'                 => $this->x,
            'y'                 => $this->y,
            'reporter'          => $this->reporter,
            'point_type'        => $this->point_type,
            'point_id'          => $this->point_id,
            /**
             * @var ScoutCustomPoint|SpawnPoint
             */
            'point'             => $this->point,
            'instance_number'   => $this->instance_number,
            'mob_id'            => $this->mob_id,
            'mob'               => $this->mob,
        ];
    }
}
