<?php

namespace Database\Seeders;

use App\Enums\Account\TeamRole;
use App\Models\User;
use App\Support\SafeNames;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->createTeamRoles();
        $this->createDemoUsers();
        $this->createDummyUsers();
    }

    /**
     * Create team role definitions.
     */
    private function createTeamRoles(): void
    {
        foreach (TeamRole::cases() as $teamRole) {
            Role::findOrCreate($teamRole->value, 'web');
        }
    }

    /**
     * Create demo credential users with full profile details.
     */
    private function createDemoUsers(): void
    {
        $demoUsers = [
            [
                'name' => config('demo.demo_user_name'),
                'email' => config('demo.demo_user_email'),
                'password' => config('demo.demo_user_password'),
            ],
        ];

        foreach ($demoUsers as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);

            $this->fillProfile($user);
        }
    }

    /**
     * Create 10 dummy users with full profile details using safe names.
     */
    private function createDummyUsers(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $first = fake()->randomElement(SafeNames::FIRST_NAMES);
            $last = fake()->randomElement(SafeNames::LAST_NAMES);
            $suffix = Str::lower(Str::random(4));

            $user = User::factory()->create([
                'name' => "$first $last",
                'email' => Str::lower($first) . '.' . Str::lower($last) . '.' . $suffix . '@example.com',
            ]);

            $this->fillProfile($user);
        }
    }

    /**
     * Fill a user's auto-created profile with full details.
     */
    private function fillProfile(User $user): void
    {
        [$first, $last] = explode(' ', $user->name, 2);

        $user->profile->update([
            'first_name' => $first,
            'last_name' => $last ?? '',
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'zip' => fake()->postcode(),
            'country' => fake()->country(),
            'timezone' => fake()->timezone(),
            'locale' => fake()->locale(),
            'currency' => fake()->currencyCode(),
        ]);
    }
}
