<?php

declare(strict_types=1);

namespace App\Actions\Wallet\CreateWallet;

use App\Domain\Wallet\Services\RandomWalletCodeGenerator;
use App\Domain\Wallet\Wallet;
use App\Models\Wallet as WalletModel;
use App\Support\AuditLog;

final class CreateWallet
{
    public function execute(CreateWalletInput $input): Wallet
    {
        /** @var ?WalletModel  $userWallet */
        $userWallet = WalletModel::query()->where('user_id', $input->userId)->first();
        if (null !== $userWallet) {
            AuditLog::log('wallet.create.failed', [
                'user_id' => (int) $input->userId,
                'reason' => 'already_exists',
            ]);
            return $userWallet->toDomainEntity();
        }

        $codeGenerator = new RandomWalletCodeGenerator();
        $wallet = Wallet::create(userId: $input->userId, codeGenerator: $codeGenerator);

        $walletModel = WalletModel::fromDomainEntity($wallet);
        $walletModel->save();

        $wallet->setId($walletModel->id);

        AuditLog::log('wallet.create.successded', [
            'user_id' => (int) $input->userId,
            'code' => $wallet->getCode(),
        ]);

        return $wallet;
    }
}
