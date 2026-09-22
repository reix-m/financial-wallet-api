<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('user cannot verify email with a not valid hash', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $hash = sha1('invalid-email');

    $response = $this->getJson("/api/v1/auth/email/verify/{$user->id}/{$hash}");

    $response->assertForbidden();

    expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
});

it('user can verify email with a valid hash', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $hash = sha1($user->getEmailForVerification());

    $response = $this->getJson("/api/v1/auth/email/verify/{$user->id}/{$hash}");

    $response->assertNoContent();

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
});
