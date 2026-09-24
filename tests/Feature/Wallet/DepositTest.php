<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('cannot deposit when wallet not exists', function (): void {
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
    ]);

    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/wallets/deposit', ['amount' => 10000])
        ->assertNotFound();
});

it('can deposit with valid amount', function (): void {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
    ]);

    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);
    $amount = 10000;
    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/wallets/deposit', ['amount' => $amount])
        ->assertOk()
        ->assertJsonPath('data.code', $wallet->code)
        ->assertJsonPath('data.balance', 'R$ 200,00');
});
