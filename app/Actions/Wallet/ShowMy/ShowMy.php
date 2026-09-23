<?php

declare(strict_types=1);

namespace App\Actions\Wallet\ShowMy;

use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Wallet;
use App\Models\Wallet as WalletModel;

final class ShowMy
{
    public function execute(int $userId): Wallet
    {
        /** @var ?WalletModel $walletModel */
        $walletModel = WalletModel::query()->where('user_id', $userId)->first();

        if ( ! $walletModel) {
            throw new WalletNotFoundException();
        }

        return $walletModel->toDomainEntity();
    }
}
