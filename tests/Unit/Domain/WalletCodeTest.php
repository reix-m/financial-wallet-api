<?php

declare(strict_types=1);

use App\Domain\Wallet\ValueObjects\WalletCode;

it('can create with parameters', function (): void {
    $value = '123456';
    $walletCode = new WalletCode(value: $value);

    expect($walletCode->getValue())->toBe($value);
});
