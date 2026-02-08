<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'link' => $this->link,
            'read_at' => $this->read_at?->toISOString(),
            'sender' => $this->when($this->relationLoaded('sender') && $this->sender, fn (): array => [
                'id' => (string) $this->sender->id,
                'name' => $this->sender->name,
            ]),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
