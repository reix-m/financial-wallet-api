<?php

declare(strict_types=1);

namespace App\Actions\Wallet\ListTransactions;

final readonly class ListTransactionsInput
{
    public function __construct(public int $userId, public int $perPage = 15) {}
}
