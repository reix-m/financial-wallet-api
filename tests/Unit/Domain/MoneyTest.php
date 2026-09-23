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
