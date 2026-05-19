<?php

use App\Support\SecurityRules;
use Illuminate\Support\Facades\Validator;

test('strong password rules require length mixed case numbers and symbols', function () {
    $validator = Validator::make([
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ], [
        'password' => SecurityRules::strongPassword(),
    ]);

    expect($validator->fails())->toBeTrue();
});

test('strong password rules accept a complex password', function () {
    $validator = Validator::make([
        'password' => 'FanousSecure#2026',
        'password_confirmation' => 'FanousSecure#2026',
    ], [
        'password' => SecurityRules::strongPassword(),
    ]);

    expect($validator->passes())->toBeTrue();
});

test('suspicious request payloads are blocked', function () {
    $this->get('/?next=../.env')->assertStatus(400);
});

test('security headers are present on web responses', function () {
    $this->get('/login')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
});
