<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Account\TeamStatus;
use App\Models\Account\Profile;
use App\Models\Account\Team;
use App\Models\Account\TeamInvitation;
use App\Traits\AcceptsTerms;
use App\Traits\HasSettings;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use AcceptsTerms, HasApiTokens, HasFactory, HasRoles, HasSettings, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
        ];
    }

    /**
     * The profile that belongs to the user.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * The teams that belong to the user.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'users_teams')
            ->withPivot('is_admin')
            ->withTimestamps();
    }

    /**
     * The team invitations for the user.
     */
    public function teamInvitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class, 'user_id');
    }

    /**
     * Get the current team.
     */
    public function currentTeam(): ?Team
    {
        return $this->teams()->where('key', $this->setting('current_team'))->first();
    }

    /**
     * The "booting" method of the model.
     */
    protected static function booted(): void
    {
        // Create a profile and default team for the user when the user is created.
        static::created(function (User $user): void {
            $name = explode(' ', $user->name);
            $user->profile()->create([
                'first_name' => $name[0],
                'last_name' => $name[1] ?? null,
            ]);

            $team_name = $name[0] . "'s Team";

            $team = Team::create([
                'key' => Str::slug($team_name),
                'label' => $team_name,
                'status' => TeamStatus::ACTIVE,
            ]);

            $user->teams()->attach($team->id, ['is_admin' => true]);

            $user->setSetting('current_team', $team->key);
        });

        // Delete the user's profile when the user is deleted.
        static::deleted(function (User $user): void {
            $user->profile()->delete();
        });

    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }
}
