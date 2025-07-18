<?php

namespace App\Services;

use App\Models\Account\Profile;
use App\Models\Account\Terms\Agreement;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class AccountService
{
    /**
     * The user
     */
    protected User $user;

    /**
     * Set the user.
     */
    public function setUser(User|string $identifier): AccountService
    {
        $this->user = $identifier instanceof User
            ? $identifier
            : (
                filter_var($identifier, FILTER_VALIDATE_EMAIL)
                    ? $this->getUserByEmail($identifier)
                    : $this->getUserBySlug($identifier)
            );

        return $this;
    }

    /**
     * Get the project.
     *
     * @throws Exception
     */
    public function getUser(): User
    {
        if (empty($this->user)) {
            throw new Exception('No user set.');
        }

        return $this->user;
    }

    /**
     * Set up the terms.
     *
     * @throws Exception
     */
    public function setupTerms(): Model
    {
        return $this->getUser()
            ->terms_agreements()
            ->firstOrCreate(
                ['terms_version_id' => config('terms.current_version')],
            );
    }

    /**
     * Accept the terms.
     *
     * @throws Exception
     */
    public function acceptTerms(Agreement $terms): bool
    {
        return $terms->update([
            'accepted_at' => now(),
        ]);
    }

    /**
     * Check if the user has accepted the terms.
     *
     * @throws Exception
     */
    public function hasAcceptedTerms(): bool
    {
        return $this->getUser()
            ->terms_agreements()
            ->where('terms_version_id', config('terms.current_version'))
            ->whereNotNull('accepted_at')
            ->exists();
    }

    /**
     * Check if the user has violated the terms.
     *
     * @throws Exception
     */
    public function hasViolatedTerms(): bool
    {
        return $this->getUser()
            ->terms_violations()
            ->where('terms_version_id', config('terms.current_version'))
            ->exists();
    }

    /**
     * Create a user.
     *
     * @throws Exception
     */
    public function createUser(array $validated): User
    {
        // Create the user.
        if (! ($user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'email_verified_at' => now(),
            'password' => Hash::make($this->generatePassword()),
        ]))) {
            throw new Exception('Unable to create user.');
        }

        // Add the role to the user.
        $user->assignRole($validated['role']);

        return $user;
    }

    /**
     * Generate a password.
     */
    public function generatePassword(): string
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+'), 0, 12);
    }

    /**
     * Get the user by email.
     */
    public function getUserByEmail(string $email): User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Get the user by slug.
     */
    public function getUserBySlug(string $slug): User
    {
        return Profile::where('public_id', $slug)->first()->user;
    }
}
