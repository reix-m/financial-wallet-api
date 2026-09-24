<?php

declare(strict_types=1);

use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\Exceptions\InvalidTransactionAmountException;
use App\Domain\Wallet\Services\RandomWalletCodeGenerator;
use App\Domain\Wallet\Transaction;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\TransactionStatus;
use App\Domain\Wallet\ValueObjects\TransactionType;
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

it('can set id', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $wallet = Wallet::create(userId: 1, codeGenerator: $codeGenerator);

    expect($wallet->getId())->toBeNull();
    $wallet->setId(10);
    expect($wallet->getId())->toBe(10);
});

it('cannot deposit zero or negative amount', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $wallet = Wallet::create(userId: 1, codeGenerator: $codeGenerator);
    $exception = null;

    try {
        $wallet->deposit(Money::fromCents(-300));
    } catch (Exception $e) {
        $exception = $e;
    }

    expect($exception)->not()->toBeNull();
    expect($exception)->toBeInstanceOf(InvalidTransactionAmountException::class);
});

it('can deposit positive amount', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $wallet = Wallet::create(userId: 1, codeGenerator: $codeGenerator);
    $exception = null;

    $wallet->deposit(Money::fromCents(300));

    expect($wallet->getBalance()->toCents())->toBe(300);
});

it('cannot withdraw zero or negative amount', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $wallet = Wallet::create(userId: 1, codeGenerator: $codeGenerator);
    $exception = null;

    try {
        $wallet->withdraw(Money::fromCents(-300));
    } catch (Exception $e) {
        $exception = $e;
    }

    expect($exception)->not()->toBeNull();
    expect($exception)->toBeInstanceOf(InvalidTransactionAmountException::class);
});

it('cannot withdraw without sufficient funds', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $wallet = Wallet::create(userId: 1, codeGenerator: $codeGenerator, initialBalance: Money::fromCents(100));
    $exception = null;

    try {
        $wallet->withdraw(Money::fromCents(3300));
    } catch (Exception $e) {
        $exception = $e;
    }

    expect($exception)->not()->toBeNull();
    expect($exception)->toBeInstanceOf(InsufficientBalanceException::class);
});

it('cannot revert credit zero or negative amount', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $wallet = Wallet::create(userId: 1, codeGenerator: $codeGenerator);
    $exception = null;

    try {
        $wallet->revertCredit(Money::fromCents(-300));
    } catch (Exception $e) {
        $exception = $e;
    }

    expect($exception)->not()->toBeNull();
    expect($exception)->toBeInstanceOf(InvalidTransactionAmountException::class);
});

it('can revert credit positive amount', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $wallet = Wallet::create(userId: 1, codeGenerator: $codeGenerator, initialBalance: Money::fromCents(1000));

    $wallet->revertCredit(Money::fromCents(300));

    expect($wallet->getBalance()->toCents())->toBe(700);
});

it('can revert credit leaving negative balance', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $wallet = Wallet::create(userId: 1, codeGenerator: $codeGenerator, initialBalance: Money::fromCents(100));

    $wallet->revertCredit(Money::fromCents(300));

    expect($wallet->getBalance()->toCents())->toBe(-200);
});

it('cannot compensate transaction from another wallet', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $wallet = Wallet::create(userId: 1, codeGenerator: $codeGenerator);
    $wallet->setId(10);
    $transaction = new Transaction(
        status: TransactionStatus::COMPLETED,
        type: TransactionType::DEPOSIT,
        amount: Money::fromCents(300),
        walletId: 20,
        id: 1,
    );
    $exception = null;

    try {
        $wallet->compensate($transaction);
    } catch (Exception $e) {
        $exception = $e;
    }

    expect($exception)->not()->toBeNull();
    expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
});

it('can compensate a credit transaction by debiting the wallet', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $wallet = Wallet::create(userId: 1, codeGenerator: $codeGenerator, initialBalance: Money::fromCents(1000));
    $wallet->setId(10);
    $transaction = new Transaction(
        status: TransactionStatus::COMPLETED,
        type: TransactionType::DEPOSIT,
        amount: Money::fromCents(300),
        walletId: 10,
        id: 1,
    );

    $wallet->compensate($transaction);

    expect($wallet->getBalance()->toCents())->toBe(700);
});

it('can compensate a debit transaction by crediting the wallet', function (): void {
    $codeGenerator = new RandomWalletCodeGenerator();
    $wallet = Wallet::create(userId: 1, codeGenerator: $codeGenerator, initialBalance: Money::fromCents(1000));
    $wallet->setId(10);
    $transaction = new Transaction(
        status: TransactionStatus::COMPLETED,
        type: TransactionType::TRANSFER_OUT,
        amount: Money::fromCents(300),
        walletId: 10,
        id: 1,
        relatedTransactionId: 2,
    );

    $wallet->compensate($transaction);

    expect($wallet->getBalance()->toCents())->toBe(1300);
});
