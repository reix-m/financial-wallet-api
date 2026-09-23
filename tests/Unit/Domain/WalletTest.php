<?php

declare(strict_types=1);

use App\Domain\Wallet\Services\RandomWalletCodeGenerator;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletCode;
use App\Domain\Wallet\Wallet;
use Exception;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

it('cannot create wallet without user id', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $exception = null;

    try {
        $userId = (int) null;
        $wallet = Wallet::create(userId: $userId, codeGenerator: $codeGenerator);
    } catch (Exception $e) {
        $exception = $e;
    }

    expect($exception)->not()->toBeNull();
    expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
});

it('can create wallet without optional parameters', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $userId = 1;
    $wallet = Wallet::create(userId: $userId, codeGenerator: $codeGenerator);

    expect($wallet->getUserId())->toBe($userId);
    expect($wallet->getCode())->toBeInstanceOf(WalletCode::class);
    expect(gettype($wallet->getCode()->getValue()))->toBe('string');
    expect($wallet->getBalance())->toBeInstanceOf(Money::class);
    expect($wallet->getBalance()->toCents())->toBe(0);
    expect($wallet->getCreatedAt())->toBeInstanceOf(Carbon::class);
});

it('can create wallet with optional parameters', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $userId = 1;
    $balanceValue = 1200000;
    $wallet = Wallet::create(
        userId: $userId,
        codeGenerator: $codeGenerator,
        initialBalance: Money::fromCents($balanceValue),
    );

    expect($wallet->getUserId())->toBe($userId);
    expect($wallet->getCode())->toBeInstanceOf(WalletCode::class);
    expect(gettype($wallet->getCode()->getValue()))->toBe('string');
    expect($wallet->getBalance())->toBeInstanceOf(Money::class);
    expect($wallet->getBalance()->toCents())->toBe($balanceValue);
});

it('cannot set already assigned id', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $wallet = Wallet::create(userId: 1, codeGenerator: $codeGenerator);
    $exception = null;

    $wallet->setId(10);

    try {
        $wallet->setId(10);
    } catch (Exception $e) {
        $exception = $e;
    }

    expect($wallet->getId())->toBe(10);
    expect($exception)->not()->toBeNull();
    expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
});

it('can set it', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $wallet = Wallet::create(userId: 1, codeGenerator: $codeGenerator);

    expect($wallet->getId())->toBeNull();
    $wallet->setId(10);
    expect($wallet->getId())->toBe(10);
});
