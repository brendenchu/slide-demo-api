<?php

namespace App\Models\Account;

use App\Enums\TeamStatus;
use App\Models\Story\Project;
use App\Models\User;
use App\Traits\AcceptsTerms;
use App\Traits\HasPublicId;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        return $this->belongsToMany(User::class, 'users_teams');
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
        // Create a default team for the user when the user is created.
        static::created(function (Team $team): void {
            // set public id
            $team->key = Str::slug($team->key . '-' . $team->public_id);
            $team->save();
        });
    }
}
