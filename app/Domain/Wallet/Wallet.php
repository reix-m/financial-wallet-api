<?php

declare(strict_types=1);

namespace App\Domain\Wallet;

use App\Domain\Wallet\Contracts\WalletCodeGeneratorInterface;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\Exceptions\InvalidTransactionAmountException;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletCode;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

final class Wallet
{
    private ?int $id;
    private WalletCode $code;
    private int $userId;
    private Carbon $createdAt;
    private Money $balance;

    public function __construct(WalletCode $code, int $userId, ?Money $balance, ?int $id = null, ?Carbon $createdAt = null)
    {
        if (empty($userId)) {
            throw new InvalidArgumentException('The user id is required.');
        }

        $this->code = $code;
        $this->id = $id;
        $this->userId = $userId;
        $this->balance = $balance ?? Money::zero();
        $this->createdAt = $createdAt ?? now();
    }

    public static function create(int $userId, WalletCodeGeneratorInterface $codeGenerator, ?Money $initialBalance = null): self
    {
        return new self(
            userId: $userId,
            code: $codeGenerator->generate(),
            balance: $initialBalance,
        );
    }

    public function deposit(Money $amount): void
    {
        if ( ! $amount->isPositive()) {
            throw new InvalidTransactionAmountException($this->code->getValue());
        }

        $this->balance = $this->balance->add($amount);
    }

    public function withdraw(Money $amount): void
    {
        if ( ! $amount->isPositive()) {
            throw new InvalidTransactionAmountException($this->code->getValue());
        }

        if ($this->balance->isLessThan($amount)) {
            throw new InsufficientBalanceException($this->code->getValue());
        }

        $this->balance = $this->balance->subtract($amount);
    }

    public function revertCredit(Money $amount): void
    {
        if ( ! $amount->isPositive()) {
            throw new InvalidTransactionAmountException($this->code->getValue());
        }

        $this->balance = $this->balance->subtract($amount);
    }

    public function compensate(Transaction $transaction): void
    {
        if ($transaction->getWalletId() !== $this->id) {
            throw new InvalidArgumentException('The transaction does not belong to this wallet.');
        }

        if ($transaction->getType()->isCredit()) {
            $this->revertCredit($transaction->getAmount());
        } else {
            $this->deposit($transaction->getAmount());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): WalletCode
    {
        return $this->code;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getBalance(): Money
    {
        return $this->balance;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    public function setId(int $id): void
    {
        if (null !== $this->id) {
            throw new InvalidArgumentException('The wallet id is already assigned.');
        }

        $this->id = $id;
    }
}
