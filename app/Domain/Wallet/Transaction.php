<?php

declare(strict_types=1);

namespace App\Domain\Wallet;

use App\Domain\Wallet\Exceptions\CannotRevertTransactionException;
use App\Domain\Wallet\Exceptions\TransactionAlreadyReversedException;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\TransactionStatus;
use App\Domain\Wallet\ValueObjects\TransactionType;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

final class Transaction
{
    private ?int $id;
    private TransactionStatus $status;
    private TransactionType $type;
    private int $walletId;
    private ?int $relatedTransactionId;
    private Carbon $createdAt;
    private Money $amount;

    public function __construct(
        TransactionStatus $status,
        TransactionType $type,
        Money $amount,
        int $walletId,
        ?int $id = null,
        ?int $relatedTransactionId = null,
        ?Carbon $createdAt = null,
    ) {
        if (empty($walletId)) {
            throw new InvalidArgumentException('The wallet id is required.');
        }

        if ($type->isTransfer() && null === $relatedTransactionId) {
            throw new InvalidArgumentException('Transfer transactions require a related transaction id.');
        }

        $this->id = $id;
        $this->walletId = $walletId;
        $this->amount = $amount;
        $this->type = $type;
        $this->status = $status;
        $this->relatedTransactionId = $relatedTransactionId;
        $this->createdAt = $createdAt ?? now();
    }

    public static function reversalFor(Transaction $original): self
    {
        if (null === $original->getId()) {
            throw new InvalidArgumentException('Only persisted transactions can be reversed.');
        }

        return new self(
            status: TransactionStatus::COMPLETED,
            type: TransactionType::REVERSAL,
            amount: $original->getAmount(),
            walletId: $original->getWalletId(),
            relatedTransactionId: $original->getId(),
        );
    }

    public function reverse(): void
    {
        if (null === $this->id) {
            throw new InvalidArgumentException('Only persisted transactions can be reversed.');
        }

        if (TransactionStatus::REVERSED === $this->status) {
            throw new TransactionAlreadyReversedException((string) $this->id);
        }

        if (TransactionType::REVERSAL === $this->type) {
            throw new CannotRevertTransactionException((string) $this->id);
        }

        $this->status = TransactionStatus::REVERSED;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): TransactionStatus
    {
        return $this->status;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function getWalletId(): int
    {
        return $this->walletId;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getRelatedTransactionId(): ?int
    {
        return $this->relatedTransactionId;
    }

    public function setId(int $id): void
    {
        if (null !== $this->id) {
            throw new InvalidArgumentException('The transaction id is already assigned.');
        }

        $this->id = $id;
    }
}
