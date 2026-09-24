<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\TransactionStatus;
use App\Domain\Wallet\ValueObjects\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
final class TransactionFactory extends Factory
{
    /** @var class-string<Transaction> */
    protected $model = Transaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory()->create()->id,
            'amount' => Money::fromCents(cents: 10000),
            'type' => TransactionType::DEPOSIT,
            'status' => TransactionStatus::COMPLETED,
        ];
    }
}
