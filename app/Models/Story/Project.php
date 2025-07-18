<?php

namespace App\Models\Story;

use App\Enums\ProjectStatus;
use App\Models\Account\Team;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static find(mixed $storyId)
 * @method static where(string $string, string $project)
 */
class Project extends Model
{
    use HasPublicId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'label',
        'description',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array<int, string>
     */
    protected $with = [
        'teams',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'slug',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
        ];
    }

    /**
     * The "booting" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->status = ProjectStatus::DRAFT;
        });
    }

    /**
     * Get the project's slug.
     */
    public function getSlugAttribute(): string
    {
        return $this->key;
    }

    /**
     * The teams that belong to the project.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'teams_projects');
    }

    /**
     * Get the user-submitted responses for the project.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Get the project's tokens
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }

    /**
     * Get the project's token for the authenticated user
     */
    public function user_token(): ?string
    {
        if (! ($tokens = $this->tokens()
            ->where('user_id', auth()->user()->id)
            ->where('expires_at', '>', now())
            ->whereNull('revoked_at')
            ->first())) {
            return null;
        }

        return $tokens->public_id;
    }
}
