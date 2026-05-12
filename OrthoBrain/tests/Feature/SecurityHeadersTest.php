<?php

// Regression guard: pins the Permissions-Policy contract so a
// future security-hardening pass cannot silently re-block voice
// input or camera capture. See CLAUDE.md Entry 12.

it('allows camera and microphone for same origin in Permissions-Policy', function () {
    $response = $this->get(route('login'));

    $response->assertHeader('Permissions-Policy');

    $policy = $response->headers->get('Permissions-Policy');

    expect($policy)
        ->toContain('camera=(self)')
        ->toContain('microphone=(self)')
        ->toContain('geolocation=()')
        ->toContain('interest-cohort=()');
});
