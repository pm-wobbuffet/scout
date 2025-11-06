<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpansionResource extends JsonResource
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
            /**
             * The ID of the expansion, corresponds to the key of the ExVersion Sheet.
             */
            'id'            => $this->id,
            /**
             * @var string
             */
            'name'          => $this->name,
            /**
             * The abbreviation used for navigation menus
             * @var string
             */
            'abbreviation'  => $this->abbreviation,
            /**
             * The total number of active zones assigned to this expansion
             * @var int
             */
            'zone_count'    => $this->whenCounted('zones'),
        ];
    }
}
