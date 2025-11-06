<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SpawnPointCollection extends ResourceCollection
{
    public $collects = SpawnPointResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return SpawnPointResource[]
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
