<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('cannot authenticate with invalid email', function (): void {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'foo.bar@example.com',
        'password' => 'StrongP@ss123',
    ]);

    $response->assertUnauthorized();
});

it('cannot authenticate with invalid password', function (): void {
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
        'password' => 'StrongP@ss123',
    ]);
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'InvalidPass',
    ]);

    $response->assertUnauthorized();
});

it('can authenticate with valid email and password', function (): void {
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
        'password' => 'StrongP@ss123',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'StrongP@ss123',
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at'],
            'access_token', 'token_type', 'expires_at',
        ])
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('data.email', 'foo.bar@example.com');

});
