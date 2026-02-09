<?php

namespace Tests;

use App\Enums\Account\TeamRole;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Initialize global team context for Spatie permissions (0 = no team scope)
        setPermissionsTeamId(0);

        // Seed team role definitions for Spatie
        foreach (TeamRole::cases() as $role) {
            Role::findOrCreate($role->value, 'web');
        }

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
