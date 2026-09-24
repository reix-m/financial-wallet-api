<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

final class TransactionNotFoundException extends TransactionDomainException
{
    public function __construct(?string $identifier = null)
    {
        $message = null !== $identifier && '' !== $identifier
            ? "The transaction '{$identifier}' was not found."
            : "The transaction was not found.";

        parent::__construct($message, $identifier);
    }
}
