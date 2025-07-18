<?php

namespace App\Models\Story;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Response extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'step',
    ];

    /**
     * Get the project that owns the response.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
