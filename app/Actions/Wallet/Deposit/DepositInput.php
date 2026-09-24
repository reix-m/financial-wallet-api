<?php

declare(strict_types=1);

namespace App\Actions\Wallet\Deposit;

final readonly class DepositInput
{
    public function __construct(public int $amount, public int $userId) {}
}
