<?php

use App\Models\Account\Plan;
use App\Models\Account\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('has a plan relationship', function (): void {
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->create(['plan_id' => $plan->id]);

    expect($subscription->plan)->toBeInstanceOf(Plan::class)
        ->and($subscription->plan->id)->toBe($plan->id);
});

it('has an accountable polymorphic relationship', function (): void {
    $user = User::factory()->create();
    $subscription = Subscription::factory()->create([
        'accountable_type' => User::class,
        'accountable_id' => $user->id,
    ]);

    expect($subscription->accountable)->toBeInstanceOf(User::class)
        ->and($subscription->accountable->id)->toBe($user->id);
});

it('casts datetime fields correctly', function (): void {
    $subscription = Subscription::factory()->create([
        'trial_ends_at' => now()->addDays(7),
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    expect($subscription->trial_ends_at)->toBeInstanceOf(Carbon::class)
        ->and($subscription->starts_at)->toBeInstanceOf(Carbon::class)
        ->and($subscription->ends_at)->toBeInstanceOf(Carbon::class);
});

it('identifies active subscriptions correctly', function (): void {
    $activeSubscription = Subscription::factory()->create([
        'status' => 'active',
        'ends_at' => now()->addMonth(),
        'canceled_at' => null,
    ]);

    expect($activeSubscription->isActive())->toBeTrue();
});

it('identifies inactive subscription when status is not active', function (): void {
    $inactiveSubscription = Subscription::factory()->create([
        'status' => 'inactive',
        'ends_at' => now()->addMonth(),
        'canceled_at' => null,
    ]);

    expect($inactiveSubscription->isActive())->toBeFalse();
});

it('identifies inactive subscription when expired', function (): void {
    $expiredSubscription = Subscription::factory()->create([
        'status' => 'active',
        'ends_at' => now()->subDay(),
        'canceled_at' => null,
    ]);

    expect($expiredSubscription->isActive())->toBeFalse();
});

it('identifies inactive subscription when canceled', function (): void {
    $canceledSubscription = Subscription::factory()->create([
        'status' => 'active',
        'ends_at' => now()->addMonth(),
        'canceled_at' => now(),
    ]);

    expect($canceledSubscription->isActive())->toBeFalse();
});

it('identifies subscriptions on trial correctly', function (): void {
    $trialSubscription = Subscription::factory()->create([
        'trial_ends_at' => now()->addDays(7),
    ]);

    expect($trialSubscription->onTrial())->toBeTrue();
});

it('identifies subscriptions not on trial when trial ended', function (): void {
    $subscription = Subscription::factory()->create([
        'trial_ends_at' => now()->subDay(),
    ]);

    expect($subscription->onTrial())->toBeFalse();
});

it('identifies subscriptions not on trial when no trial', function (): void {
    $subscription = Subscription::factory()->create([
        'trial_ends_at' => null,
    ]);

    expect($subscription->onTrial())->toBeFalse();
});

it('identifies canceled subscriptions correctly', function (): void {
    $canceledSubscription = Subscription::factory()->create([
        'canceled_at' => now(),
    ]);

    expect($canceledSubscription->isCanceled())->toBeTrue();
});

it('identifies non-canceled subscriptions correctly', function (): void {
    $activeSubscription = Subscription::factory()->create([
        'canceled_at' => null,
    ]);

    expect($activeSubscription->isCanceled())->toBeFalse();
});
