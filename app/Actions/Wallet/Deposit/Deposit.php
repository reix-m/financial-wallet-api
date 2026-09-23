<?php

declare(strict_types=1);

namespace App\Actions\Wallet\Deposit;

use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\TransactionStatus;
use App\Domain\Wallet\ValueObjects\TransactionType;
use App\Domain\Wallet\Wallet;
use App\Models\Transaction;
use App\Models\Wallet as WalletModel;
use Illuminate\Support\Facades\DB;

final class Deposit
{
    public function execute(DepositInput $input): Wallet
    {
        $amount = Money::fromCents($input->amount);

        return DB::transaction(function () use ($input, $amount): Wallet {
            /** @var ?WalletModel $walletModel */
            $walletModel = WalletModel::query()->where('user_id', $input->userId)->lockForUpdate()->first();

            if ( ! $walletModel) {
                throw new WalletNotFoundException();
            }

            $wallet = $walletModel->toDomainEntity();
            $wallet->deposit($amount);

            $walletModel->balance = $wallet->getBalance();
            $walletModel->save();

            Transaction::create([
                'wallet_id' => $wallet->getId(),
                'type' => TransactionType::DEPOSIT,
                'amount' => $amount,
                'status' => TransactionStatus::COMPLETED,
            ]);

            return $wallet;
        });
    }
}
