<?php

declare(strict_types=1);

namespace App\Actions\Wallet\CreateWallet;

use App\Domain\Wallet\Services\RandomWalletCodeGenerator;
use App\Domain\Wallet\Wallet;
use App\Models\Wallet as WalletModel;
use App\Support\AuditLog;

final class CreateWallet
{
    public function execute(CreateWalletInput $input): ?Wallet
    {
        if (WalletModel::query()->where('user_id', $input->userId)->exists()) {
            AuditLog::log('wallet.create.failed', [
                'user_id' => (int) $input->userId,
                'reason' => 'already_exists',
            ]);
            return null;
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
