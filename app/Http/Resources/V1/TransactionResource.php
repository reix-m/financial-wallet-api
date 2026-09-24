<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Domain\Wallet\ValueObjects\TransactionStatus;
use App\Domain\Wallet\ValueObjects\TransactionType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Transaction $transaction */
        $transaction = $this->resource;

        /** @var TransactionType $type */
        $type = $transaction->type;

        $isReversal = TransactionType::REVERSAL === $type;
        $counterpartyWallet = $isReversal ? null : $transaction->counterpartyWallet;

        return [
            'id' => $transaction->id,
            'type' => $this->parseType($type),
            'direction' => $this->parseDirection($transaction),
            'amount' => $transaction->amount->toCents(),
            'formatted_amount' => $transaction->amount->formatted(),
            'status' => $this->parseStatus($transaction->status),
            'created_at' => $transaction->created_at?->toAtomString(),
            'counterparty' => null !== $counterpartyWallet
                ? CounterpartyResource::make($counterpartyWallet)
                : null,
            'reversed_transaction' => $isReversal && null !== $transaction->relatedTransaction
                ? TransactionResource::make($transaction->relatedTransaction)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function parseStatus(TransactionStatus $status): array
    {
        return match ($status) {
            TransactionStatus::COMPLETED => ['value' => $status->value, 'label' => 'Realizada'],
            TransactionStatus::REVERSED => ['value' => $status->value, 'label' => 'Estornada'],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function parseType(TransactionType $type): array
    {
        return match ($type) {
            TransactionType::DEPOSIT => ['value' => $type->value, 'label' => 'Depósito'],
            TransactionType::TRANSFER_OUT => ['value' => $type->value, 'label' => 'Transferência Enviada'],
            TransactionType::TRANSFER_IN => ['value' => $type->value, 'label' => 'Transferência Recebida'],
            TransactionType::REVERSAL => ['value' => $type->value, 'label' => 'Estorno'],
        };
    }

    private function parseDirection(Transaction $transaction): string
    {
        if (TransactionType::REVERSAL !== $transaction->type) {
            return $transaction->type->isCredit() ? 'credit' : 'debit';
        }

        $originalType = $transaction->relatedTransaction?->type;

        if (null === $originalType) {
            return 'debit';
        }

        return $originalType->isCredit() ? 'debit' : 'credit';
    }
}
