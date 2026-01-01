<?php

namespace App\Traits;

use App\Interfaces\Uuidable;
use Illuminate\Support\Str;

trait HasPublicId
{
    /**
     * The "booting" method of the trait.
     */
    protected static function bootHasPublicId(): void
    {
        static::creating(function ($model): void {
            $publicId = $model instanceof Uuidable ? Str::lower(Str::uuid()) : Str::random(12);
            $model->public_id = $publicId;
        });
    }

    /**
     * Get the public id attribute.
     */
    public function getPublicIdAttribute(): string
    {
        return $this->attributes['public_id'];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
