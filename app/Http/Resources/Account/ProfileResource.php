<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Return empty array if profile is missing/not loaded
        if ($this->resource === null) {
            return [];
        }

        return [
            'id' => $this->public_id,
            'name' => trim($this->first_name . ' ' . $this->last_name),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
            'currency' => $this->currency,
        ];
    }
}
