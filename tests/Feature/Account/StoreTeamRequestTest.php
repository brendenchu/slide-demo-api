<?php

use App\Http\Requests\Account\StoreTeamRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('blocks unauthenticated requests in FormRequest', function (): void {
    // Testing the FormRequest authorization directly since route may not exist
    $request = new StoreTeamRequest;
    $request->setUserResolver(fn (): null => null);

    expect($request->authorize())->toBeFalse();
});

it('allows authenticated users to access team creation', function (): void {
    $user = User::factory()->create();

    // Note: This test verifies authorization passes
    // Actual route may not exist - testing the FormRequest authorization only
    $this->actingAs($user);

    $request = new StoreTeamRequest;
    $request->setUserResolver(fn () => $user);

    expect($request->authorize())->toBeTrue();
});

it('blocks unauthenticated users from team creation', function (): void {
    $request = new StoreTeamRequest;
    $request->setUserResolver(fn (): null => null);

    expect($request->authorize())->toBeFalse();
});

it('requires team name in validation rules', function (): void {
    $user = User::factory()->create();

    $request = new StoreTeamRequest;
    $rules = $request->rules();

    expect($rules)->toHaveKey('name')
        ->and($rules['name'])->toContain('required')
        ->and($rules['name'])->toContain('string')
        ->and($rules['name'])->toContain('max:255');
});

it('allows optional team key in validation rules', function (): void {
    $request = new StoreTeamRequest;
    $rules = $request->rules();

    expect($rules)->toHaveKey('key')
        ->and($rules['key'])->toContain('nullable')
        ->and($rules['key'])->toContain('unique:teams,key');
});
