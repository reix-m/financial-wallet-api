<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

final class CannotTransferToSelfException extends WalletDomainException
{
    public function __construct(?string $identifier = null)
    {
        parent::__construct("Transfers to your own wallet is not allowed.", $identifier);
    }
}
