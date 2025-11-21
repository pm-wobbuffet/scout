<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ScoutVersion;

/**
 * Transform a ScoutVersion into a browser-friendly object
 * @var $this \App\Models\ScoutVersion
 */
class ScoutVersionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'version' => $this->version,
            'created_at' => $this->created_at,
        ];
    }
}
