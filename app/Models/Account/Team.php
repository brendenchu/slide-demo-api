<?php

namespace App\Models\Account;

use App\Enums\Account\TeamStatus;
use App\Models\Story\Project;
use App\Models\User;
use App\Traits\AcceptsTerms;
use App\Traits\HasPublicId;
use Database\Factories\Account\TeamFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property mixed $label
 */
class Team extends Model
{
    use AcceptsTerms, HasFactory, HasPublicId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'label',
        'description',
        'status',
        'email',
        'phone',
        'website',
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
            'status' => TeamStatus::class,
        ];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return TeamFactory::new();
    }

    /**
     * Get the team's slug.
     */
    public function getSlugAttribute(): string
    {
        return $this->key;
    }

    /**
     * The users that belong to the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_teams')
            ->withPivot('is_admin')
            ->withTimestamps();
    }

    /**
     * The invitations for the team.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    /**
     * Check if a user is an admin of this team.
     */
    public function isAdmin(User $user): bool
    {
        return $this->users()
            ->where('users.id', $user->id)
            ->wherePivot('is_admin', true)
            ->exists();
    }

    /**
     * The projects that belong to the team.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'teams_projects');
    }

    /**
     * The "booting" method of the model.
     */
    protected static function booted(): void
    {
        // Set the slug key before the team is created (saves one DB write)
        static::creating(function (Team $team): void {
            if (empty($team->key)) {
                $team->key = Str::slug($team->label);
            }
        });

        // Append public_id to key after creation (when public_id is available)
        static::created(function (Team $team): void {
            // Only update if key doesn't already include the public_id
            if (! Str::contains($team->key, $team->public_id)) {
                $team->key = Str::slug($team->key . '-' . $team->public_id);
                $team->saveQuietly(); // Use saveQuietly to avoid triggering events again
            }
        });
    }
}
