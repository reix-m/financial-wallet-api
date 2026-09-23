<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletCode;
use App\Domain\Wallet\Wallet as DomainWallet;
use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $code
 * @property Money $balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable(['id', 'code', 'balance', 'user_id', 'created_at'])]
#[Hidden([])]
final class Wallet extends Model
{
    /** @use HasFactory<WalletFactory> */
    use HasFactory;

    public static function fromDomainEntity(DomainWallet $wallet): self
    {
        $attributes = [
            'user_id' => $wallet->getUserId(),
            'code' => $wallet->getCode()->getValue(),
            'balance' => $wallet->getBalance(),
            'created_at' => $wallet->getCreatedAt(),
        ];

        if (null !== $wallet->getId()) {
            $attributes['id'] = $wallet->getId();
        }

        return new self($attributes);
    }

    public function toDomainEntity(): DomainWallet
    {
        return new DomainWallet(
            id: (int) $this->id,
            code: new WalletCode((string) $this->code),
            userId: (int) $this->user_id,
            balance: $this->balance,
            createdAt: $this->created_at,
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => MoneyCast::class,
        ];
    }
}
