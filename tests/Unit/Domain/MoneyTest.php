<?php

declare(strict_types=1);

use App\Domain\Wallet\ValueObjects\Money;

it('can create zero money', function (): void {
    $money = Money::zero();

    expect($money->toCents())->toBe(0);
});

it('can create from cents', function (): void {
    $cents = 1200000;
    $money = Money::fromCents($cents);

    expect($money->toCents())->toBe($cents);
});

it('can get float value', function (): void {
    $cents = 1200050;
    $money = Money::fromCents($cents);

    expect($money->toFloat())->toBe(12000.50);
});

it('can get formatted value', function (): void {
    $cents = 1200050;
    $money = Money::fromCents($cents);

    expect($money->formatted())->toBe('R$ 12.000,50');
});

it('should return true when value is negative', function (): void {
    $cents = -13240;
    $money = Money::fromCents($cents);

    expect($money->isNegative())->toBeTrue();
});

it('should return false when value is not negative', function (): void {
    $cents = 13240;
    $money = Money::fromCents($cents);

    expect($money->isNegative())->toBeFalse();
});

it('should return true when values is equals', function (): void {
    $cents = -13240;
    $money = Money::fromCents($cents);

    expect($money->equals(Money::fromCents($cents)))->toBeTrue();
});

it('should return false when values is not equals', function (): void {
    $cents = 13240;
    $money = Money::fromCents($cents);

    expect($money->equals(Money::fromCents(13241)))->toBeFalse();
});

it('should return true when value is positive', function (): void {
    $cents = 13240;
    $money = Money::fromCents($cents);

    expect($money->isPositive())->toBeTrue();
});

it('should return false when value is not positive', function (): void {
    $cents = -13240;
    $money = Money::fromCents($cents);

    expect($money->isPositive())->toBeFalse();
});

it('can add money', function (): void {
    $cents = 13240;
    $money = Money::fromCents($cents);

    expect($money->add(Money::fromCents(100))->toCents())->toBe(13340);
});

it('should return true when value is less than other', function (): void {
    $cents = 12340;
    $money = Money::fromCents($cents);
    $other = Money::fromCents($cents + 100);

    expect($money->isLessThan($other))->toBeTrue();
});

it('should return false when value is not less than other', function (): void {
    $cents = 12340;
    $money = Money::fromCents($cents);
    $other = Money::fromCents($cents - 100);

    expect($money->isLessThan($other))->toBeFalse();
});

it('can subtract money', function (): void {
    $cents = 13240;
    $money = Money::fromCents($cents);

    expect($money->subtract(Money::fromCents(100))->toCents())->toBe(13140);
});
