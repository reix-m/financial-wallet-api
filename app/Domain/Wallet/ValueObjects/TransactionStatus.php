<?php

declare(strict_types=1);

namespace App\Domain\Wallet\ValueObjects;

enum TransactionStatus: string
{
    case COMPLETED = 'completed';
    case REVERSED = 'reversed';
}
