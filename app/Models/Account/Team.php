<?php

namespace App\Models\Account;

use App\Enums\Account\TeamRole;
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
        'is_personal',
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
            'is_personal' => 'boolean',
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
     * Check if a user is the owner of this team.
     */
    public function isOwner(User $user): bool
    {
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($this->id);
        $result = $user->hasRole(TeamRole::Owner);
        setPermissionsTeamId($previousTeamId);

        return $result;
    }

    /**
     * The users that belong to the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_teams')
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
     *
     * The owner is implicitly an admin.
     */
    public function isAdmin(User $user): bool
    {
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($this->id);
        $result = $user->hasRole([TeamRole::Owner, TeamRole::Admin]);
        setPermissionsTeamId($previousTeamId);

        return $result;
    }

    /**
     * Assign a team role to a user, removing any existing team roles first.
     */
    public function assignTeamRole(User $user, TeamRole $role): void
    {
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($this->id);

        $user->removeRole(TeamRole::Owner);
        $user->removeRole(TeamRole::Admin);
        $user->removeRole(TeamRole::Member);
        $user->assignRole($role);

        setPermissionsTeamId($previousTeamId);
    }

    /**
     * Get the team role for a user.
     */
    public function getUserTeamRole(User $user): ?TeamRole
    {
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($this->id);

        $result = null;
        foreach (TeamRole::cases() as $role) {
            if ($user->hasRole($role)) {
                $result = $role;
                break;
            }
        }

        setPermissionsTeamId($previousTeamId);

        return $result;
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
