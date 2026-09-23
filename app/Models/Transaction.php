<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\TransactionStatus;
use App\Domain\Wallet\ValueObjects\TransactionType;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property int $id
 * @property int $wallet_id
 * @property int $related_transaction_id
 * @property TransactionType $type
 * @property Money $amount
 * @property TransactionStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable(['id', 'wallet_id', 'related_transaction_id', 'type', 'amount', 'status'])]
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
}
