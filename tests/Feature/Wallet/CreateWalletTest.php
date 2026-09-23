<?php

declare(strict_types=1);

use App\Actions\Wallet\CreateWallet\CreateWallet;
use App\Actions\Wallet\CreateWallet\CreateWalletInput;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('cannot create duplicated wallet', function (): void {
    $createWallet = new CreateWallet();
    $user = User::factory()->create();
    $walletModel = Wallet::factory()->create(['user_id' => $user->id]);

    $input = new CreateWalletInput(userId: $user->id);
    $wallet = $createWallet->execute($input);

    expect($wallet->getCode()->getValue())->toBe($walletModel->code);
    expect($wallet->getCode()->getValue())->toBe($walletModel->code);
    expect($wallet->getUserId())->toBe($walletModel->user_id);
    expect($wallet->getBalance()->toCents())->toBe($walletModel->balance->toCents());
});

it('can create user wallet', function (): void {
    $createWallet = new CreateWallet();
    $user = User::factory()->create();

    $input = new CreateWalletInput(userId: $user->id);
    $wallet = $createWallet->execute($input);

    expect($wallet)->not()->toBeNull();

    /** @var Wallet $createdWallet */
    $createdWallet = Wallet::query()->where('id', $wallet->getId())->first();
    expect($wallet->getCode()->getValue())->toBe($createdWallet->code);
    expect($wallet->getUserId())->toBe($createdWallet->user_id);
    expect($wallet->getBalance()->toCents())->toBe($createdWallet->balance->toCents());
});
