<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can return authenticated user', function (): void {
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
    ]);

    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.type', 'users')
        ->assertJsonPath('data.id', (string) $user->id)
        ->assertJsonPath('data.attributes.email', 'foo.bar@example.com');
});
