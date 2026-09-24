<?php

declare(strict_types=1);

use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('replays the deposit response for a repeated idempotency key', function (): void {
    /** @var User $user */
    $user = User::factory()->create(['email' => 'foo.bar@example.com']);
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'balance' => Money::fromCents(0),
    ]);
    $token = $user->createToken('access-token')->plainTextToken;
    $headers = ['Idempotency-Key' => 'deposit-key-1'];

    $this->withToken($token)->withHeaders($headers)
        ->postJson('/api/v1/wallets/deposit', ['amount' => 10000])
        ->assertOk()
        ->assertJsonPath('data.balance', 'R$ 100,00');

    $this->withToken($token)->withHeaders($headers)
        ->postJson('/api/v1/wallets/deposit', ['amount' => 10000])
        ->assertOk()
        ->assertHeader('Idempotency-Relayed', 'deposit-key-1')
        ->assertJsonPath('data.balance', 'R$ 100,00');

    expect(Transaction::query()
        ->where('wallet_id', $wallet->id)
        ->where('type', TransactionType::DEPOSIT)
        ->count())->toBe(1);
});

it('processes two deposits when the idempotency keys differ', function (): void {
    /** @var User $user */
    $user = User::factory()->create(['email' => 'foo.bar@example.com']);
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'balance' => Money::fromCents(0),
    ]);
    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)->withHeaders(['Idempotency-Key' => 'deposit-key-a'])
        ->postJson('/api/v1/wallets/deposit', ['amount' => 10000])
        ->assertOk();

    $this->withToken($token)->withHeaders(['Idempotency-Key' => 'deposit-key-b'])
        ->postJson('/api/v1/wallets/deposit', ['amount' => 10000])
        ->assertOk()
        ->assertJsonPath('data.balance', 'R$ 200,00');

    expect(Transaction::query()
        ->where('wallet_id', $wallet->id)
        ->where('type', TransactionType::DEPOSIT)
        ->count())->toBe(2);
});

it('does not duplicate a transfer when the same key is retried', function (): void {
    /** @var User $sender */
    $sender = User::factory()->create(['email' => 'sender@example.com']);
    /** @var User $receiver */
    $receiver = User::factory()->create(['email' => 'receiver@example.com']);
    /** @var Wallet $senderWallet */
    $senderWallet = Wallet::factory()->create([
        'user_id' => $sender->id,
        'balance' => Money::fromCents(10000),
    ]);
    /** @var Wallet $receiverWallet */
    $receiverWallet = Wallet::factory()->create([
        'user_id' => $receiver->id,
        'balance' => Money::fromCents(0),
    ]);
    $token = $sender->createToken('access-token')->plainTextToken;
    $payload = ['target_account_code' => $receiverWallet->code, 'amount' => 5000];
    $headers = ['Idempotency-Key' => 'transfer-key-1'];

    $this->withToken($token)->withHeaders($headers)
        ->postJson('/api/v1/wallets/transfer', $payload)
        ->assertOk();

    $this->withToken($token)->withHeaders($headers)
        ->postJson('/api/v1/wallets/transfer', $payload)
        ->assertOk()
        ->assertHeader('Idempotency-Relayed', 'transfer-key-1');

    $receiverWallet->refresh();

    expect($receiverWallet->balance->toCents())->toBe(5000);
    expect(Transaction::query()
        ->where('wallet_id', $senderWallet->id)
        ->where('type', TransactionType::TRANSFER_OUT)
        ->count())->toBe(1);
});

it('rejects reusing the same key on a different route', function (): void {
    /** @var User $user */
    $user = User::factory()->create(['email' => 'foo.bar@example.com']);
    Wallet::factory()->create(['user_id' => $user->id, 'balance' => Money::fromCents(10000)]);
    /** @var Wallet $target */
    $target = Wallet::factory()->create(['balance' => Money::fromCents(0)]);
    $token = $user->createToken('access-token')->plainTextToken;
    $headers = ['Idempotency-Key' => 'shared-key'];

    $this->withToken($token)->withHeaders($headers)
        ->postJson('/api/v1/wallets/deposit', ['amount' => 10000])
        ->assertOk();

    $this->withToken($token)->withHeaders($headers)
        ->postJson('/api/v1/wallets/transfer', ['target_account_code' => $target->code, 'amount' => 1000])
        ->assertUnprocessable()
        ->assertJsonPath('error', 'MismatchedPathException');
});

it('processes the request when no idempotency key is provided', function (): void {
    /** @var User $user */
    $user = User::factory()->create(['email' => 'foo.bar@example.com']);
    Wallet::factory()->create(['user_id' => $user->id, 'balance' => Money::fromCents(0)]);
    $token = $user->createToken('access-token')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/wallets/deposit', ['amount' => 10000])
        ->assertOk()
        ->assertJsonPath('data.balance', 'R$ 100,00');
});
