<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

final class TransactionAlreadyReversedException extends TransactionDomainException
{
    public function __construct(?string $identifier = null)
    {
        parent::__construct('The transaction has already been reversed.', $identifier);
    }
}
