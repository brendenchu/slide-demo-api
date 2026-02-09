<?php

namespace App\Http\Resources;

use App\Http\Resources\Account\ProfileResource;
use App\Http\Resources\Account\TeamResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * NOTE: For optimal performance, eager-load these relationships in your controller:
     * - profile
     * - teams
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...(new ProfileResource($this->whenLoaded('profile')))->toArray($request),
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'name' => $this->name,
            'team' => $this->when($this->relationLoaded('teams'), fn () => TeamResource::make($this->currentTeam())),
        ];
    }
}
