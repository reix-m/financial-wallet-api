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

        $counterpartyWallet = $transaction->relatedTransaction?->wallet;

        return [
            'id' => (string) $transaction->id,
            'type' => $this->parseType($type),
            'is_credit' => $type->isCredit(),
            'amount' => $transaction->amount->toCents(),
            'formatted_amount' => $transaction->amount->formatted(),
            'status' => $this->parseStatus($transaction->status),
            'created_at' => $transaction->created_at?->toAtomString(),
            'counterparty' => null !== $counterpartyWallet
                ? CounterpartyResource::make($counterpartyWallet)
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
}
