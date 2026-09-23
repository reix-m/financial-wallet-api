<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Contracts;

use App\Domain\Wallet\ValueObjects\WalletCode;

interface WalletCodeGeneratorInterface
{
    public function generate(): WalletCode;
}
