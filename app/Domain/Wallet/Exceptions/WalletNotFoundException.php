<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

final class WalletNotFoundException extends WalletDomainException
{
    public function __construct(?string $identifier = null)
    {
        $message = null !== $identifier && '' !== $identifier
            ? "The wallet '{$identifier}' was not found."
            : "The wallet was not found.";

        parent::__construct($message, $identifier);
    }
}
