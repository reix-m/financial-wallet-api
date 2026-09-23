<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

final class InsufficientBalanceException extends WalletDomainException
{
    public function __construct(?string $identifier = null)
    {
        parent::__construct("Insufficient funds to complete the transaction.", $identifier);
    }
}
