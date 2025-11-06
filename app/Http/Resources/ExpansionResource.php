<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Representation of a full Expansion resource for use in the browser
 * Trims unnecessary fields but leaves relations intact.
 * Makes for a smaller build to send to Inertia client
 */
class ExpansionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'abbreviation'  => $this->abbreviation,
            'zones'         => ExpansionZoneResource::collection($this->whenLoaded('zones')),
        ];
    }
}
