<?php

declare(strict_types=1);

namespace App\Domain\Wallet\ValueObjects;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case REVERSAL = 'reversal';

    public function isCredit(): bool
    {
        return match ($this) {
            self::DEPOSIT => true,
            default => false,
        };
    }
}
