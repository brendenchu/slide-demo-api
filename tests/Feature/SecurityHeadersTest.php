<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('applies CSP headers to API endpoints', function (): void {
    // Test a public API endpoint (login doesn't require auth)
    $response = $this->getJson('/api/v1/auth/login');

    // Login endpoint should have CSP headers
    $response->assertHeader('Content-Security-Policy');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Permissions-Policy');
});

it('skips CSP headers for API documentation route when accessing in local environment', function (): void {
    // Set environment to local to bypass RestrictedDocsAccess middleware
    config(['app.env' => 'local']);

    $response = $this->get('/docs/api');

    // Should not have CSP header (skipped for docs routes)
    $response->assertHeaderMissing('Content-Security-Policy');

    // Other security headers should still be present
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Permissions-Policy');
});

it('skips CSP headers for API documentation JSON route', function (): void {
    config(['app.env' => 'local']);

    $response = $this->get('/docs/api.json');

    // Should not have CSP header (skipped for docs routes)
    $response->assertHeaderMissing('Content-Security-Policy');

    // Other security headers should still be present
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
});

it('has correct CSP directives on protected routes', function (): void {
    $response = $this->getJson('/api/v1/auth/login');

    $csp = $response->headers->get('Content-Security-Policy');

    expect($csp)->toContain("default-src 'self'");
    expect($csp)->toContain("script-src 'self' 'unsafe-inline' 'unsafe-eval'");
    expect($csp)->toContain("style-src 'self' 'unsafe-inline'");
    expect($csp)->toContain("img-src 'self' data: https:");
    expect($csp)->toContain("font-src 'self' data:");
    expect($csp)->toContain("connect-src 'self'");
    expect($csp)->toContain("frame-ancestors 'none'");
});

it('applies all security headers except CSP to all routes', function (): void {
    // Test that non-CSP headers are always present
    $response = $this->getJson('/api/v1/auth/login');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
});
