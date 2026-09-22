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
        ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'access_token', 'token_type', 'expires_at'])
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.name', 'Foo Bar')
        ->assertJsonPath('user.email', 'foo.bar@example.com');

    $user = User::query()->where('email', 'foo.bar@example.com')->first();

    expect($user)->not->toBeNull();
    Notification::assertSentTo($user, VerifyEmail::class);
});
