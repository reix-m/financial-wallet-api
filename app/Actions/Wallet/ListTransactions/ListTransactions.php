<?php

declare(strict_types=1);

namespace App\Actions\Wallet\ListTransactions;

use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListTransactions
{
    /**
     * @return LengthAwarePaginator<int, Transaction>
     */
    public function execute(ListTransactionsInput $input): LengthAwarePaginator
    {
        /** @var ?Wallet $wallet */
        $wallet = Wallet::query()->where('user_id', $input->userId)->first();

        if ( ! $wallet) {
            throw new WalletNotFoundException();
        }

        return Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->forListing()
            ->orderBy('created_at', 'desc')
            ->paginate($input->perPage);
    }
}
