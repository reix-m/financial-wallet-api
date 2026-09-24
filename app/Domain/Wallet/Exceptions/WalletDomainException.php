<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

use DomainException;

abstract class WalletDomainException extends DomainException
{
    public ?string $identifier = null;

    public function __construct(string $message, ?string $identifier = null)
    {
        parent::__construct($message);
        $this->identifier = $identifier;
    }
}
