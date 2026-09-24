<?php

declare(strict_types=1);

namespace App\Actions\Wallet\Transfer;

use App\Domain\Wallet\Exceptions\CannotTransferToSelfException;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\TransactionStatus;
use App\Domain\Wallet\ValueObjects\TransactionType;
use App\Domain\Wallet\Wallet;
use App\Models\Transaction;
use App\Models\Wallet as WalletModel;
use Illuminate\Support\Facades\DB;

final class Transfer
{
    public function execute(TransferInput $input): Wallet
    {
        $amount = Money::fromCents($input->amount);

        return DB::transaction(function () use ($input, $amount): Wallet {
            $wallets = WalletModel::query()
                ->where('code', $input->targetAccountCode)
                ->orWhere('user_id', $input->userId)
                ->lockForUpdate()
                ->get()
                ->keyBy(fn($wallet) => $wallet->code);

            /** @var ?WalletModel $senderModel */
            $senderModel = $wallets->first(fn($w) => $w->user_id === $input->userId);
            /** @var ?WalletModel $receiverModel */
            $receiverModel = $wallets->first(fn($w) => $w->code === $input->targetAccountCode);

            if ( ! $senderModel) {
                throw new WalletNotFoundException();
            }

            if ( ! $receiverModel) {
                throw new WalletNotFoundException($input->targetAccountCode);
            }

            if ($senderModel->id === $receiverModel->id) {
                throw new CannotTransferToSelfException($senderModel->code);
            }

            $senderWallet = $senderModel->toDomainEntity();
            $receiverWallet = $receiverModel->toDomainEntity();

            $senderWallet->withdraw($amount);
            $receiverWallet->deposit($amount);

            $senderModel->balance = $senderWallet->getBalance();
            $senderModel->save();

            $receiverModel->balance = $receiverWallet->getBalance();
            $receiverModel->save();

            $outTransaction = Transaction::create([
                'wallet_id' => $senderWallet->getId(),
                'type' => TransactionType::TRANSFER_OUT,
                'amount' => $amount,
                'status' => TransactionStatus::COMPLETED,
            ]);

            $inTransaction = Transaction::create([
                'wallet_id' => $receiverWallet->getId(),
                'related_transaction_id' => $outTransaction->id,
                'type' => TransactionType::TRANSFER_IN,
                'amount' => $amount,
                'status' => TransactionStatus::COMPLETED,
            ]);

            $outTransaction->update([
                'related_transaction_id' => $inTransaction->id,
            ]);

            return $senderWallet;
        });
    }
}
