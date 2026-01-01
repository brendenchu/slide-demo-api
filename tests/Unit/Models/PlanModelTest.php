<?php

use App\Models\Account\Plan;
use App\Models\Account\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('has a subscriptions relationship', function (): void {
    $plan = Plan::factory()->create();
    $subscription1 = Subscription::factory()->create(['plan_id' => $plan->id]);
    $subscription2 = Subscription::factory()->create(['plan_id' => $plan->id]);

    expect($plan->subscriptions)->toHaveCount(2)
        ->and($plan->subscriptions->first())->toBeInstanceOf(Subscription::class);
});

it('has an active subscriptions relationship', function (): void {
    $plan = Plan::factory()->create();
    Subscription::factory()->create([
        'plan_id' => $plan->id,
        'status' => 'active',
        'canceled_at' => null,
    ]);
    Subscription::factory()->create([
        'plan_id' => $plan->id,
        'status' => 'inactive',
        'canceled_at' => null,
    ]);
    Subscription::factory()->create([
        'plan_id' => $plan->id,
        'status' => 'active',
        'canceled_at' => now(),
    ]);

    expect($plan->activeSubscriptions)->toHaveCount(1)
        ->and($plan->activeSubscriptions->first()->status)->toBe('active');
});

it('casts fields correctly', function (): void {
    $plan = Plan::factory()->create([
        'price' => 29.99,
        'trial_period' => 14,
        'is_active' => true,
        'features' => ['feature1', 'feature2'],
    ]);

    expect($plan->price)->toBeString()->toBe('29.99')
        ->and($plan->trial_period)->toBeInt()
        ->and($plan->is_active)->toBeBool()
        ->and($plan->features)->toBeArray()
        ->and($plan->created_at)->toBeInstanceOf(Carbon::class);
});

it('filters active plans with scope', function (): void {
    Plan::factory()->create(['is_active' => true]);
    Plan::factory()->create(['is_active' => true]);
    Plan::factory()->create(['is_active' => false]);

    $activePlans = Plan::active()->get();

    expect($activePlans)->toHaveCount(2)
        ->and($activePlans->every(fn ($plan): bool => $plan->is_active === true))->toBeTrue();
});

it('filters plans by interval with scope', function (): void {
    Plan::factory()->create(['interval' => 'monthly']);
    Plan::factory()->create(['interval' => 'monthly']);
    Plan::factory()->create(['interval' => 'yearly']);

    $monthlyPlans = Plan::interval('monthly')->get();

    expect($monthlyPlans)->toHaveCount(2)
        ->and($monthlyPlans->every(fn ($plan): bool => $plan->interval === 'monthly'))->toBeTrue();
});

it('identifies plans with trial correctly', function (): void {
    $planWithTrial = Plan::factory()->create(['trial_period' => 14]);

    expect($planWithTrial->hasTrial())->toBeTrue();
});

it('identifies plans without trial correctly', function (): void {
    $planWithoutTrial = Plan::factory()->create(['trial_period' => 0]);

    expect($planWithoutTrial->hasTrial())->toBeFalse();
});

it('identifies active plans correctly', function (): void {
    $activePlan = Plan::factory()->create(['is_active' => true]);

    expect($activePlan->isActive())->toBeTrue();
});

it('identifies inactive plans correctly', function (): void {
    $inactivePlan = Plan::factory()->create(['is_active' => false]);

    expect($inactivePlan->isActive())->toBeFalse();
});

it('formats price correctly', function (): void {
    $plan = Plan::factory()->create(['price' => 29.99]);

    expect($plan->formattedPrice())->toBe('$29.99');
});

it('formats price with custom currency symbol', function (): void {
    $plan = Plan::factory()->create(['price' => 29.99]);

    expect($plan->formattedPrice('€'))->toBe('€29.99');
});

it('can chain scopes', function (): void {
    Plan::factory()->create(['is_active' => true, 'interval' => 'monthly']);
    Plan::factory()->create(['is_active' => true, 'interval' => 'yearly']);
    Plan::factory()->create(['is_active' => false, 'interval' => 'monthly']);

    $activeMonthlyPlans = Plan::active()->interval('monthly')->get();

    expect($activeMonthlyPlans)->toHaveCount(1)
        ->and($activeMonthlyPlans->first()->is_active)->toBeTrue()
        ->and($activeMonthlyPlans->first()->interval)->toBe('monthly');
});
