<?php

declare(strict_types=1);

namespace App\Actions\Wallet\RevertTransaction;

final readonly class RevertTransactionInput
{
    public function __construct(public int $transactionId, public int $userId) {}
}
