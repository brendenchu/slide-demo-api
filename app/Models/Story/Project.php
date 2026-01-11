<?php

namespace App\Models\Story;

use App\Enums\Story\ProjectStatus;
use App\Models\Account\Team;
use App\Models\User;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static find(mixed $storyId)
 * @method static where(string $string, string $project)
 */
class Project extends Model
{
    use HasFactory, HasPublicId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'key',
        'label',
        'description',
        'status',
        'current_step',
        'responses',
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
            'responses' => 'array',
        ];
    }

    /**
     * The "booting" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($model): void {
            $model->status = ProjectStatus::Draft;
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
     * Get the user that owns the project.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * Get the current user's active token for this project
     */
    public function userToken(): HasMany
    {
        return $this->tokens()
            ->where('user_id', auth()->id())
            ->where('expires_at', '>', now())
            ->whereNull('revoked_at');
    }

    /**
     * Get the project's token for the authenticated user
     *
     * @deprecated Use userToken() relationship instead for better performance
     */
    public function user_token(): ?string
    {
        $token = $this->userToken()->first();

        return $token?->public_id;
    }
}
