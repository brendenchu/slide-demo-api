<?php

namespace App\Services;

use App\Models\Account\Profile;
use App\Models\Account\Terms\Agreement;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountService
{
    /**
     * The currently active user for this service instance.
     */
    protected User $user;

    /**
     * Set the user by User instance, email, or slug.
     *
     * @param  User|string  $identifier  User instance, email address, or profile slug
     * @return AccountService Fluent interface for method chaining
     *
     * @throws Exception If user cannot be found
     */
    public function setUser(User|string $identifier): self
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
     * Get the currently set user.
     *
     * @return User The active user instance
     *
     * @throws Exception If no user has been set
     */
    public function getUser(): User
    {
        if (empty($this->user)) {
            throw new Exception('No user set.');
        }

        return $this->user;
    }

    /**
     * Set up the terms agreement for the current user.
     *
     * Creates a new terms agreement record if one doesn't exist for the current version.
     *
     * @return Agreement The terms agreement model instance
     *
     * @throws Exception If no user has been set
     */
    public function setupTerms(): Agreement
    {
        return $this->getUser()
            ->terms_agreements()
            ->firstOrCreate(
                ['terms_version_id' => config('terms.current_version')],
            );
    }

    /**
     * Mark the given terms agreement as accepted.
     *
     * @param  Agreement  $terms  The terms agreement to accept
     * @return bool True if successfully updated
     */
    public function acceptTerms(Agreement $terms): bool
    {
        return $terms->update([
            'accepted_at' => now(),
        ]);
    }

    /**
     * Check if the current user has accepted the current terms version.
     *
     * @return bool True if user has accepted current terms
     *
     * @throws Exception If no user has been set
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
     * Check if the current user has violated the current terms version.
     *
     * @return bool True if user has violations on record
     *
     * @throws Exception If no user has been set
     */
    public function hasViolatedTerms(): bool
    {
        return $this->getUser()
            ->terms_violations()
            ->where('terms_version_id', config('terms.current_version'))
            ->exists();
    }

    /**
     * Create a new user with the given validated data.
     *
     * Auto-generates a secure password and assigns the specified role.
     * Also triggers User model events which create profile and default team.
     *
     * @param  array  $validated  Must contain: first_name, last_name, email, role
     * @return User The newly created user instance
     *
     * @throws Exception If user creation fails
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
     * Generate a cryptographically secure random password.
     *
     * Uses Laravel's Str::random() which is cryptographically secure,
     * unlike str_shuffle() which is not suitable for security purposes.
     *
     * @return string A 16-character random password
     */
    public function generatePassword(): string
    {
        return Str::random(16);
    }

    /**
     * Find a user by email address.
     *
     * @param  string  $email  The email address to search for
     * @return User|null The user if found, null otherwise
     */
    public function getUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Find a user by their profile slug (public_id).
     *
     * @param  string  $slug  The profile public_id to search for
     * @return User The user associated with the profile
     *
     * @throws Exception If no profile found with the given slug
     */
    public function getUserBySlug(string $slug): User
    {
        $profile = Profile::where('public_id', $slug)->first();

        if (! $profile) {
            throw new Exception("Profile not found for slug: {$slug}");
        }

        return $profile->user;
    }
}
