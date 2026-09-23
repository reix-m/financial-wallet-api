<?php

declare(strict_types=1);

namespace App\Actions\Wallet\CreateWallet;

final readonly class CreateWalletInput
{
    public function __construct(public int $userId, public ?int $initialBalance = null) {}
}
