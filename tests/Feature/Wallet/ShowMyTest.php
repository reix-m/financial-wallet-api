<?php

declare(strict_types=1);

use App\Domain\Wallet\ValueObjects\Money;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('cannot show when wallet not exists', function (): void {
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
    ]);

    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/wallets/show/my')
        ->assertNotFound();
});

it('can show when exists', function (): void {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
    ]);

    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => Money::fromCents(20000)]);
    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/wallets/my')
        ->assertOk()
        ->assertJsonPath('data.attributes.code', $wallet->code)
        ->assertJsonPath('data.attributes.balance', 'R$ 200,00');
});
