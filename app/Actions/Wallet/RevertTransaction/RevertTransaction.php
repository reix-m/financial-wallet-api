<?php

declare(strict_types=1);

namespace App\Actions\Wallet\RevertTransaction;

use App\Domain\Wallet\Exceptions\TransactionNotFoundException;
use App\Domain\Wallet\Transaction;
use App\Domain\Wallet\Wallet;
use App\Models\Transaction as TransactionModel;
use App\Models\Wallet as WalletModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class RevertTransaction
{
    public function execute(RevertTransactionInput $input): Wallet
    {
        return DB::transaction(function () use ($input): Wallet {
            $targetModel = $this->resolveTargetTransaction($input);
            $transactionModels = $this->lockTransactionPair($targetModel);
            $walletModels = $this->lockWallets($transactionModels);
            $wallets = $this->revertTransactions($transactionModels, $walletModels);

            return $wallets[$targetModel->wallet_id];
        });
    }

    private function resolveTargetTransaction(RevertTransactionInput $input): TransactionModel
    {
        /** @var ?TransactionModel $transaction */
        $transaction = TransactionModel::query()->whereKey($input->transactionId)->first();

        if ( ! $transaction) {
            throw new TransactionNotFoundException(identifier: (string) $input->transactionId);
        }

        if ((int) $transaction->wallet()->value('user_id') !== $input->userId) {
            throw new TransactionNotFoundException(identifier: (string) $input->transactionId);
        }

        return $transaction;
    }

    /**
     * @return Collection<int, TransactionModel>
     */
    private function lockTransactionPair(TransactionModel $target): Collection
    {
        $transactionIds = array_values(array_filter([
            $target->id,
            $target->related_transaction_id,
        ]));

        /** @var Collection<int, TransactionModel> $transactions */
        $transactions = TransactionModel::query()
            ->whereIn('id', $transactionIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($transactions->count() !== count($transactionIds)) {
            throw new TransactionNotFoundException();
        }

        return $transactions;
    }

    /**
     * @param  Collection<int, TransactionModel>  $transactionModels
     * @return Collection<int, WalletModel>
     */
    private function lockWallets(Collection $transactionModels): Collection
    {
        $walletIds = $transactionModels->pluck('wallet_id')->unique()->values()->all();

        /** @var Collection<int, WalletModel> $wallets */
        $wallets = WalletModel::query()
            ->whereIn('id', $walletIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        return $wallets;
    }

    /**
     * @param  Collection<int, TransactionModel>  $transactionModels
     * @param  Collection<int, WalletModel>  $walletModels
     * @return array<int, Wallet>
     */
    private function revertTransactions(Collection $transactionModels, Collection $walletModels): array
    {
        /** @var array<int, Wallet> $wallets */
        $wallets = [];

        foreach ($transactionModels as $transactionModel) {
            $transaction = $transactionModel->toDomainEntity();
            $transaction->reverse();

            /** @var WalletModel $walletModel */
            $walletModel = $walletModels[$transaction->getWalletId()];
            $wallet = $wallets[$transaction->getWalletId()] ??= $walletModel->toDomainEntity();
            $wallet->compensate($transaction);

            $walletModel->balance = $wallet->getBalance();
            $walletModel->save();

            $transactionModel->update(['status' => $transaction->getStatus()]);

            $reversalModel = TransactionModel::fromDomainEntity(Transaction::reversalFor($transaction));
            $reversalModel->save();
        }

        return $wallets;
    }
}
