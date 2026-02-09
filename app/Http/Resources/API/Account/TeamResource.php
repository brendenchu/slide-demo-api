<?php

namespace App\Http\Resources\API\Account;

use App\Enums\Account\TeamStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Maps Team model to API response format.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id ?? (string) $this->id,
            'slug' => $this->key,
            'name' => $this->label,
            'description' => $this->description,
            'status' => $this->mapStatus(),
            'is_personal' => (bool) $this->is_personal,
            'is_owner' => auth()->check() && $this->resource->isOwner(auth()->user()),
            'is_admin' => auth()->check() && $this->resource->isAdmin(auth()->user()),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * Map TeamStatus enum to API status string.
     */
    private function mapStatus(): string
    {
        return match ($this->status) {
            TeamStatus::ACTIVE => 'active',
            TeamStatus::INACTIVE => 'inactive',
            default => 'inactive',
        };
    }
}
