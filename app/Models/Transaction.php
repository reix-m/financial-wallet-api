<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Domain\Wallet\Transaction as DomainTransaction;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\TransactionStatus;
use App\Domain\Wallet\ValueObjects\TransactionType;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property int $id
 * @property int $wallet_id
 * @property ?int $related_transaction_id
 * @property TransactionType $type
 * @property Money $amount
 * @property TransactionStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable(['id', 'wallet_id', 'related_transaction_id', 'type', 'amount', 'status', 'created_at'])]
#[Hidden([])]
final class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected $casts = [
        'amount' => MoneyCast::class,
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
    ];

    public static function fromDomainEntity(DomainTransaction $transaction): self
    {
        $attributes = [
            'status' => $transaction->getStatus(),
            'type' => $transaction->getType(),
            'amount' => $transaction->getAmount(),
            'wallet_id' => $transaction->getWalletId(),
            'related_transaction_id' => $transaction->getRelatedTransactionId(),
            'created_at' => $transaction->getCreatedAt(),
        ];

        if (null !== $transaction->getId()) {
            $attributes['id'] = $transaction->getId();
        }

        return new self($attributes);
    }

    /**
     * @return BelongsTo<Wallet, $this>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function relatedTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'related_transaction_id');
    }

    /**
     * @return HasOneThrough<Wallet, Transaction, $this>
     */
    public function counterpartyWallet(): HasOneThrough
    {
        return $this->hasOneThrough(
            Wallet::class,
            self::class,
            'id',
            'id',
            'related_transaction_id',
            'wallet_id',
        );
    }

    public function toDomainEntity(): DomainTransaction
    {
        return new DomainTransaction(
            id: $this->id,
            status: $this->status,
            type: $this->type,
            amount: $this->amount,
            walletId: $this->wallet_id,
            relatedTransactionId: $this->related_transaction_id,
            createdAt: $this->created_at,
        );
    }

    /**
     * @param  Builder<Transaction>  $query
     * @return Builder<Transaction>
     */
    public function scopeForListing(Builder $query): Builder
    {
        return $query->with([
            'counterpartyWallet' => function ($query): void {
                $query->select('wallets.id', 'user_id', 'code')
                    ->with(['user' => function ($userQuery): void {
                        $userQuery->select('id', 'name', 'email');
                    }]);
            },
            'relatedTransaction.counterpartyWallet' => function ($query): void {
                $query->select('wallets.id', 'user_id', 'code')
                    ->with(['user' => function ($userQuery): void {
                        $userQuery->select('id', 'name', 'email');
                    }]);
            },
        ]);
    }
}
