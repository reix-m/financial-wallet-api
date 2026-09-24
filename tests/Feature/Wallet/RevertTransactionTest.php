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

it('cannot revert when transaction not exists', function (): void {
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
    ]);

    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/wallets/transactions/999/revert')
        ->assertNotFound();
});

it('cannot revert transaction from another user', function (): void {
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
    ]);
    $anotherUser = User::factory()->create([
        'email' => 'another.bar@example.com',
    ]);
    $anotherWallet = Wallet::factory()->create(['user_id' => $anotherUser->id]);
    /** @var Transaction $transaction */
    $transaction = Transaction::factory()->create([
        'wallet_id' => $anotherWallet->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::COMPLETED,
    ]);

    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/wallets/transactions/' . $transaction->id . '/revert')
        ->assertNotFound();
});

it('cannot revert an already reversed transaction', function (): void {
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
    ]);
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);
    /** @var Transaction $transaction */
    $transaction = Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::REVERSED,
    ]);

    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/wallets/transactions/' . $transaction->id . '/revert')
        ->assertUnprocessable();
});

it('cannot revert a reversal transaction', function (): void {
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
    ]);
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);
    /** @var Transaction $transaction */
    $transaction = Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'type' => TransactionType::REVERSAL,
        'status' => TransactionStatus::COMPLETED,
    ]);

    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/wallets/transactions/' . $transaction->id . '/revert')
        ->assertUnprocessable();
});

it('can revert a deposit', function (): void {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'foo.bar@example.com',
    ]);
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'balance' => Money::fromCents(10000),
    ]);
    /** @var Transaction $transaction */
    $transaction = Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'type' => TransactionType::DEPOSIT,
        'amount' => Money::fromCents(10000),
        'status' => TransactionStatus::COMPLETED,
    ]);

    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/wallets/transactions/' . $transaction->id . '/revert')
        ->assertOk()
        ->assertJsonPath('data.code', $wallet->code)
        ->assertJsonPath('data.balance', 'R$ 0,00');

    $this->assertDatabaseHas('wallets', [
        'id' => $wallet->id,
        'balance' => 0,
    ]);
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'status' => TransactionStatus::REVERSED->value,
    ]);
    $this->assertDatabaseHas('transactions', [
        'wallet_id' => $wallet->id,
        'related_transaction_id' => $transaction->id,
        'type' => TransactionType::REVERSAL->value,
        'status' => TransactionStatus::COMPLETED->value,
    ]);
});

it('can revert a transfer', function (): void {
    /** @var User $sender */
    $sender = User::factory()->create([
        'email' => 'sender@example.com',
    ]);
    /** @var User $receiver */
    $receiver = User::factory()->create([
        'email' => 'receiver@example.com',
    ]);
    /** @var Wallet $senderWallet */
    $senderWallet = Wallet::factory()->create([
        'user_id' => $sender->id,
        'balance' => Money::fromCents(7000),
    ]);
    /** @var Wallet $receiverWallet */
    $receiverWallet = Wallet::factory()->create([
        'user_id' => $receiver->id,
        'balance' => Money::fromCents(3000),
    ]);
    /** @var Transaction $outTransaction */
    $outTransaction = Transaction::factory()->create([
        'wallet_id' => $senderWallet->id,
        'type' => TransactionType::TRANSFER_OUT,
        'amount' => Money::fromCents(3000),
        'status' => TransactionStatus::COMPLETED,
    ]);
    /** @var Transaction $inTransaction */
    $inTransaction = Transaction::factory()->create([
        'wallet_id' => $receiverWallet->id,
        'related_transaction_id' => $outTransaction->id,
        'type' => TransactionType::TRANSFER_IN,
        'amount' => Money::fromCents(3000),
        'status' => TransactionStatus::COMPLETED,
    ]);
    $outTransaction->update(['related_transaction_id' => $inTransaction->id]);

    $token = $sender->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/wallets/transactions/' . $outTransaction->id . '/revert')
        ->assertOk()
        ->assertJsonPath('data.code', $senderWallet->code)
        ->assertJsonPath('data.balance', 'R$ 100,00');

    $this->assertDatabaseHas('wallets', [
        'id' => $senderWallet->id,
        'balance' => 10000,
    ]);
    $this->assertDatabaseHas('wallets', [
        'id' => $receiverWallet->id,
        'balance' => 0,
    ]);
    $this->assertDatabaseHas('transactions', [
        'id' => $outTransaction->id,
        'status' => TransactionStatus::REVERSED->value,
    ]);
    $this->assertDatabaseHas('transactions', [
        'id' => $inTransaction->id,
        'status' => TransactionStatus::REVERSED->value,
    ]);
    $this->assertDatabaseHas('transactions', [
        'wallet_id' => $senderWallet->id,
        'related_transaction_id' => $outTransaction->id,
        'type' => TransactionType::REVERSAL->value,
    ]);
    $this->assertDatabaseHas('transactions', [
        'wallet_id' => $receiverWallet->id,
        'related_transaction_id' => $inTransaction->id,
        'type' => TransactionType::REVERSAL->value,
    ]);
});
