<?php

declare(strict_types=1);

namespace App\Domain\Wallet\ValueObjects;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case TRANSFER_OUT = 'transfer_out';
    case TRANSFER_IN = 'transfer_in';
    case REVERSAL = 'reversal';

    public function isCredit(): bool
    {
        return match ($this) {
            self::DEPOSIT, self::TRANSFER_IN => true,
            default => false,
        };
    }
}
