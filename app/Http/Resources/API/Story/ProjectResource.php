<?php

namespace App\Http\Resources\API\Story;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array for API consumption
     *
     * Maps Project model fields to API response format
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id ?? $this->id,
            'user_id' => $this->user?->profile?->public_id ?? (string) $this->user_id,
            'team_id' => $this->teams->first()?->public_id ?? null,
            'title' => $this->label,
            'description' => $this->description,
            'status' => $this->mapStatus(),
            'current_step' => $this->current_step ?? 'intro',
            'responses' => $this->responses ?? new \stdClass,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * Map status enum to API status strings
     */
    private function mapStatus(): string
    {
        return match ($this->status?->value ?? 1) {
            1 => 'draft',
            2 => 'in_progress',
            3 => 'completed',
            default => 'draft',
        };
    }
}
