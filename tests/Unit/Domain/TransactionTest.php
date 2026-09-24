<?php

declare(strict_types=1);

use App\Domain\Wallet\Exceptions\CannotRevertTransactionException;
use App\Domain\Wallet\Exceptions\TransactionAlreadyReversedException;
use App\Domain\Wallet\Transaction;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\TransactionStatus;
use App\Domain\Wallet\ValueObjects\TransactionType;
use Exception;
use InvalidArgumentException;

it('cannot reverse a transaction without id', function (): void {
    $transaction = new Transaction(
        status: TransactionStatus::COMPLETED,
        type: TransactionType::DEPOSIT,
        amount: Money::fromCents(1000),
        walletId: 1,
    );
    $exception = null;

    try {
        $transaction->reverse();
    } catch (Exception $e) {
        $exception = $e;
    }

    expect($exception)->not()->toBeNull();
    expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
});

it('cannot reverse an already reversed transaction', function (): void {
    $transaction = new Transaction(
        status: TransactionStatus::REVERSED,
        type: TransactionType::DEPOSIT,
        amount: Money::fromCents(1000),
        walletId: 1,
        id: 10,
    );
    $exception = null;

    try {
        $transaction->reverse();
    } catch (Exception $e) {
        $exception = $e;
    }

    expect($exception)->not()->toBeNull();
    expect($exception)->toBeInstanceOf(TransactionAlreadyReversedException::class);
});

it('cannot reverse a reversal transaction', function (): void {
    $transaction = new Transaction(
        status: TransactionStatus::COMPLETED,
        type: TransactionType::REVERSAL,
        amount: Money::fromCents(1000),
        walletId: 1,
        id: 10,
    );
    $exception = null;

    try {
        $transaction->reverse();
    } catch (Exception $e) {
        $exception = $e;
    }

    expect($exception)->not()->toBeNull();
    expect($exception)->toBeInstanceOf(CannotRevertTransactionException::class);
});

it('can reverse a completed transaction', function (): void {
    $transaction = new Transaction(
        status: TransactionStatus::COMPLETED,
        type: TransactionType::DEPOSIT,
        amount: Money::fromCents(1000),
        walletId: 1,
        id: 10,
    );

    $transaction->reverse();

    expect($transaction->getStatus())->toBe(TransactionStatus::REVERSED);
});

it('cannot create a reversal from a transaction without id', function (): void {
    $original = new Transaction(
        status: TransactionStatus::COMPLETED,
        type: TransactionType::DEPOSIT,
        amount: Money::fromCents(1000),
        walletId: 1,
    );
    $exception = null;

    try {
        Transaction::reversalFor($original);
    } catch (Exception $e) {
        $exception = $e;
    }

    expect($exception)->not()->toBeNull();
    expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
});

it('can create a reversal from an original transaction', function (): void {
    $amount = Money::fromCents(1500);
    $original = new Transaction(
        status: TransactionStatus::COMPLETED,
        type: TransactionType::DEPOSIT,
        amount: $amount,
        walletId: 7,
        id: 42,
    );

    $reversal = Transaction::reversalFor($original);

    expect($reversal->getId())->toBeNull();
    expect($reversal->getStatus())->toBe(TransactionStatus::COMPLETED);
    expect($reversal->getType())->toBe(TransactionType::REVERSAL);
    expect($reversal->getAmount()->toCents())->toBe($amount->toCents());
    expect($reversal->getWalletId())->toBe(7);
    expect($reversal->getRelatedTransactionId())->toBe(42);
});

it('can set id', function (): void {
    $transaction = new Transaction(
        status: TransactionStatus::COMPLETED,
        type: TransactionType::DEPOSIT,
        amount: Money::fromCents(1000),
        walletId: 1,
    );

    expect($transaction->getId())->toBeNull();

    $transaction->setId(10);

    expect($transaction->getId())->toBe(10);
});

it('cannot set already assigned id', function (): void {
    $transaction = new Transaction(
        status: TransactionStatus::COMPLETED,
        type: TransactionType::DEPOSIT,
        amount: Money::fromCents(1000),
        walletId: 1,
    );
    $exception = null;

    $transaction->setId(10);

    try {
        $transaction->setId(10);
    } catch (Exception $e) {
        $exception = $e;
    }

    expect($transaction->getId())->toBe(10);
    expect($exception)->not()->toBeNull();
    expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
});
