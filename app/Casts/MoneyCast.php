<?php

declare(strict_types=1);

namespace App\Casts;

use App\Domain\Wallet\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Console\Exception\InvalidOptionException;

/**
 * @implements CastsAttributes<Money, Money|int>
 */
final class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Money
    {
        return Money::fromCents((int) $value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if ($value instanceof Money) {
            return $value->toCents();
        }

        throw new InvalidOptionException('The assigned value must be an instance of Money.');
    }
}
