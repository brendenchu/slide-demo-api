<?php

namespace App\Http\Resources\Story;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * NOTE: For optimal performance, eager-load the 'userToken' relationship in your controller:
     * $projects->load('userToken')
     *
     * This will prevent N+1 queries when rendering multiple projects.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Use eager-loaded relationship if available, fallback to accessor
        $token = $this->whenLoaded('userToken', fn () => $this->userToken->first()?->public_id,
            // Fallback to accessor for backward compatibility

            fn () => $this->user_token());

        return [
            'id' => $this->public_id,
            'slug' => $this->key,
            'name' => $this->label,
            'description' => $this->description,
            'status' => $this->status->label(),
            'token' => $token,
        ];
    }
}
