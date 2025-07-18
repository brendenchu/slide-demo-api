<?php

namespace App\Models\Story;

use App\Interfaces\Uuidable;
use App\Models\User;
use App\Traits\HasPublicId;
use App\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static firstOrCreate(array $array, array $array1)
 * @method static where(string $string, Token|string $token)
 * @method static create(array $array)
 */
class Token extends Model implements Uuidable
{
    use HasPublicId, HasSettings;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'settings',
        'expires_at',
        'revoked_at',
    ];

    /**
     * Get the user that owns the Token
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project that owns the Token
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }
}
