<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
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
            'expires_at' => $this->expires_at ? $this->expires_at->toDateTimeString() : null,
            'revoked_at' => $this->revoked_at ? $this->revoked_at->toDateTimeString() : null,
            'project' => ProjectResource::make($this->project),
        ];
    }
}
