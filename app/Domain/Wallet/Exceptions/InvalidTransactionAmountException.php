<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

final class InvalidTransactionAmountException extends WalletDomainException
{
    public function __construct(?string $identifier = null)
    {
        parent::__construct("The operation amount should be greater than zero.", $identifier);
    }
}
