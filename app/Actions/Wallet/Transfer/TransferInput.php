<?php

declare(strict_types=1);

namespace App\Actions\Wallet\Transfer;

final readonly class TransferInput
{
    public function __construct(public int $userId, public string $targetAccountCode, public int $amount) {}
}
