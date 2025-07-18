<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'slug' => $this->key,
            'name' => $this->label,
            'description' => $this->description,
            'status' => $this->status->label(),
            'current' => $request->user()->currentTeam()->is($this->resource),
        ];
    }
}
