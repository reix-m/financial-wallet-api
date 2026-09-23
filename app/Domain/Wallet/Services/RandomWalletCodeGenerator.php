<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Services;

use App\Domain\Wallet\Contracts\WalletCodeGeneratorInterface;
use App\Domain\Wallet\ValueObjects\WalletCode;

final class RandomWalletCodeGenerator implements WalletCodeGeneratorInterface
{
    public function generate(): WalletCode
    {
        $randomDigits = mb_str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        return new WalletCode($randomDigits);
    }
}
