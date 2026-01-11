<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    // Create roles in database for tests
    foreach (Role::cases() as $role) {
        SpatieRole::create(['name' => $role->value, 'guard_name' => 'web']);
    }
});

it('returns list of users for admin', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $users = User::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/admin/users');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'role',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
        ]);
});

it('returns list of users for super admin', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');
    Sanctum::actingAs($superAdmin);

    User::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/admin/users');

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
        ]);
});

it('filters users by search term matching name', function (): void {
    $admin = User::factory()->create(['name' => 'Admin User']);
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user1 = User::factory()->create(['name' => 'John Doe']);
    $user2 = User::factory()->create(['name' => 'Jane Smith']);
    $user3 = User::factory()->create(['name' => 'Bob Johnson']);

    $response = $this->getJson('/api/v1/admin/users?search=John');

    $response->assertSuccessful();

    $data = $response->json('data');
    $names = array_column($data, 'name');

    expect($names)->toContain('John Doe');
    expect($names)->toContain('Bob Johnson');
    expect($names)->not->toContain('Jane Smith');
});

it('filters users by search term matching email', function (): void {
    $admin = User::factory()->create(['email' => 'admin@test.com']);
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $user1 = User::factory()->create(['email' => 'john@example.com']);
    $user2 = User::factory()->create(['email' => 'jane@example.com']);
    $user3 = User::factory()->create(['email' => 'bob@test.com']);

    $response = $this->getJson('/api/v1/admin/users?search=example.com');

    $response->assertSuccessful();

    $data = $response->json('data');
    $emails = array_column($data, 'email');

    expect($emails)->toContain('john@example.com');
    expect($emails)->toContain('jane@example.com');
    expect($emails)->not->toContain('bob@test.com');
});

it('filters users by role', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $client1 = User::factory()->create();
    $client1->assignRole('client');

    $client2 = User::factory()->create();
    $client2->assignRole('client');

    $consultant = User::factory()->create();
    $consultant->assignRole('consultant');

    $response = $this->getJson('/api/v1/admin/users?role=client');

    $response->assertSuccessful();

    $data = $response->json('data');
    expect(count($data))->toBe(2);

    $ids = array_column($data, 'id');
    expect($ids)->toContain((string) $client1->id);
    expect($ids)->toContain((string) $client2->id);
});

it('denies access to unauthenticated users', function (): void {
    $response = $this->getJson('/api/v1/admin/users');

    $response->assertUnauthorized();
});

it('includes role in user data', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $client = User::factory()->create();
    $client->assignRole('client');

    $response = $this->getJson('/api/v1/admin/users');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'role',
                ],
            ],
        ]);
});

it('returns users ordered by creation date', function (): void {
    $admin = User::factory()->create(['created_at' => now()->subDays(10)]);
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    User::factory()->create(['created_at' => now()->subDays(3)]);
    $mostRecent = User::factory()->create(['created_at' => now()->subDay()]);
    User::factory()->create(['created_at' => now()->subDays(2)]);

    $response = $this->getJson('/api/v1/admin/users');

    $response->assertSuccessful();

    $data = $response->json('data');
    expect($data[0]['id'])->toBe((string) $mostRecent->id);
});
