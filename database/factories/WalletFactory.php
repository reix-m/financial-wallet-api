<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Wallet\ValueObjects\Money;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Wallet> */
final class WalletFactory extends Factory
{
    /** @var class-string<Wallet> */
    protected $model = Wallet::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = (string) fake()->numberBetween(000000, 999999);
        return [
            'code' => $code,
            'user_id' => User::factory()->create()->id,
            'balance' => Money::fromCents(cents: 10000),
        ];
    }
}
