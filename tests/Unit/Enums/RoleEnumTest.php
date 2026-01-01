<?php

use App\Enums\Role;

it('has all role cases defined', function (): void {
    $roles = Role::cases();

    expect($roles)->toHaveCount(5)
        ->and($roles)->toContain(Role::SuperAdmin)
        ->and($roles)->toContain(Role::Admin)
        ->and($roles)->toContain(Role::Consultant)
        ->and($roles)->toContain(Role::Client)
        ->and($roles)->toContain(Role::Guest);
});

it('returns correct label for each role', function (Role $role, string $expectedLabel): void {
    expect($role->label())->toBe($expectedLabel);
})->with([
    [Role::SuperAdmin, 'Super Admin'],
    [Role::Admin, 'Admin'],
    [Role::Consultant, 'Consultant'],
    [Role::Client, 'Client'],
    [Role::Guest, 'Guest'],
]);

it('guest role has a label', function (): void {
    expect(Role::Guest->label())->toBe('Guest');
});

it('all roles have labels', function (): void {
    foreach (Role::cases() as $role) {
        expect($role->label())->toBeString()->not->toBeEmpty();
    }
});

it('returns all instances via getInstances method', function (): void {
    $instances = Role::getInstances();

    expect($instances)->toHaveCount(5)
        ->and($instances)->toContain(Role::SuperAdmin)
        ->and($instances)->toContain(Role::Guest);
});
