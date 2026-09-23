<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('register user successfully and return token', function (): void {
    Notification::fake();

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Foo Bar',
        'email' => 'foo.bar@example.com',
        'password' => 'StrongP@ss123',
    ]);

    $response->assertCreated();
    $response
        ->assertJsonStructure([
            'data' => ['attributes' => ['name', 'email', 'email_verified_at', 'created_at', 'updated_at'], 'id', 'type'],
            'meta' => ['access_token', 'token_type', 'expires_at']
        ])
        ->assertJsonPath('meta.token_type', 'Bearer')
        ->assertJsonPath('data.attributes.name', 'Foo Bar')
        ->assertJsonPath('data.attributes.email', 'foo.bar@example.com');

    $user = User::query()->where('email', 'foo.bar@example.com')->first();

    expect($user)->not->toBeNull();
    Notification::assertSentTo($user, VerifyEmail::class);
});
