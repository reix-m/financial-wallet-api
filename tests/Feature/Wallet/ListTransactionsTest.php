<?php

declare(strict_types=1);

use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\TransactionStatus;
use App\Domain\Wallet\ValueObjects\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('cannot list transactions wallet not exists', function (): void {
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
    ]);

    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/wallets/transactions')
        ->assertNotFound();
});

it('can list transactions', function (): void {
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
    ]);
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'balance' => Money::fromCents(1000),
    ]);
    /** @var Transaction $transferTransaction */
    $transferTransaction = Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'type' => TransactionType::TRANSFER_OUT,
        'status' => TransactionStatus::COMPLETED,
    ]);
    /** @var Transaction $depositTransaction */
    $depositTransaction = Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::COMPLETED,
    ]);


    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/wallets/transactions')
        ->assertOk()
        ->assertJsonPath('data.0.status.value', $transferTransaction->status)
        ->assertJsonPath('data.0.type.value', $transferTransaction->type)
        ->assertJsonPath('data.0.amount', $transferTransaction->amount->toCents())
        ->assertJsonPath('data.0.is_credit', false)
        ->assertJsonPath('data.1.status.value', $depositTransaction->status)
        ->assertJsonPath('data.1.type.value', $depositTransaction->type)
        ->assertJsonPath('data.1.amount', $depositTransaction->amount->toCents())
        ->assertJsonPath('data.1.is_credit', true);
});
