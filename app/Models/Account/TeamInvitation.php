<?php

namespace App\Models\Account;

use App\Enums\Account\InvitationStatus;
use App\Models\User;
use App\Traits\HasPublicId;
use Database\Factories\Account\TeamInvitationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamInvitation extends Model
{
    use HasFactory, HasPublicId;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'invited_by',
        'user_id',
        'email',
        'token',
        'role',
        'status',
        'accepted_at',
        'expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => InvitationStatus::class,
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return TeamInvitationFactory::new();
    }

    /**
     * The team this invitation belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * The user who sent this invitation.
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * The user this invitation is for (if they have an account).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to only pending invitations.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', InvitationStatus::Pending);
    }

    /**
     * Scope to filter by email address.
     */
    public function scopeForEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', $email);
    }

    /**
     * Check if the invitation has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Check if the invitation is still pending.
     */
    public function isPending(): bool
    {
        return $this->status === InvitationStatus::Pending && ! $this->isExpired();
    }

    /**
     * Accept the invitation for a given user.
     */
    public function accept(User $user): void
    {
        $this->update([
            'status' => InvitationStatus::Accepted,
            'user_id' => $user->id,
            'accepted_at' => now(),
        ]);

        $this->team->users()->attach($user->id, [
            'is_admin' => $this->role === 'admin',
        ]);
    }
}
