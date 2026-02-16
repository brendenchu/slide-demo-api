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

        $this->call(DemoSeeder::class);
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
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => bcrypt($data['password']),
                    'email_verified_at' => now(),
                    'remember_token' => Str::random(10),
                ],
            );

            $this->fillProfile($user);
        }
    }

    /**
     * Create 10 dummy users with full profile details using safe names.
     */
    private function createDummyUsers(): void
    {
        $firstNames = SafeNames::FIRST_NAMES;
        $lastNames = SafeNames::LAST_NAMES;

        for ($i = 0; $i < 10; $i++) {
            $first = $firstNames[$i % count($firstNames)];
            $last = $lastNames[$i % count($lastNames)];
            $suffix = str_pad((string) ($i * 1111), 4, '0', STR_PAD_LEFT);

            $email = Str::lower($first) . '.' . Str::lower($last) . '.' . $suffix . '@example.com';

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => "$first $last",
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                    'remember_token' => Str::random(10),
                ],
            );

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
            'phone' => '555-' . str_pad((string) crc32($user->email), 7, '0', STR_PAD_LEFT),
            'address' => ($user->id * 100) . ' Main St',
            'city' => 'Vancouver',
            'state' => 'BC',
            'zip' => 'V6B 1A1',
            'country' => 'Canada',
            'timezone' => 'America/Vancouver',
            'locale' => 'en',
            'currency' => 'CAD',
        ]);
    }
}
