<?php

use App\Models\Account\Terms\Agreement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns false when user has no agreements', function (): void {
    $user = User::factory()->create();
    $user->terms_agreements()->delete();

    expect($user->hasAcceptedCurrentTerms())->toBeFalse();
});

it('returns true when user has accepted the current terms version', function (): void {
    $user = User::factory()->create();

    expect($user->hasAcceptedCurrentTerms())->toBeTrue();
});

it('returns false when user has declined the current terms version', function (): void {
    $user = User::factory()->create();
    $user->terms_agreements()->delete();

    Agreement::factory()->declined()->create([
        'accountable_id' => $user->id,
        'accountable_type' => $user->getMorphClass(),
        'terms_version_id' => config('terms.current_version'),
    ]);

    expect($user->hasAcceptedCurrentTerms())->toBeFalse();
});

it('returns false when user has accepted a different terms version', function (): void {
    $user = User::factory()->create();
    $user->terms_agreements()->delete();

    Agreement::factory()->create([
        'accountable_id' => $user->id,
        'accountable_type' => $user->getMorphClass(),
        'terms_version_id' => 999,
        'accepted_at' => now(),
    ]);

    expect($user->hasAcceptedCurrentTerms())->toBeFalse();
});
