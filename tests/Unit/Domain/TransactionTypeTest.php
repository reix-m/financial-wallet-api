<?php

declare(strict_types=1);
use App\Domain\Wallet\ValueObjects\TransactionType;

it('should return true when type is credit', function (): void {
    expect(TransactionType::DEPOSIT->isCredit())->toBeTrue();
});

it('should return false when type is not credit', function (): void {
    expect(TransactionType::REVERSAL->isCredit())->toBeFalse();
});
