<?php

declare(strict_types=1);

use App\Domain\Wallet\ValueObjects\TransactionType;

it('should return true when type is credit', function (): void {
    expect(TransactionType::DEPOSIT->isCredit())->toBeTrue();
});

it('should return false when type is not credit', function (): void {
    expect(TransactionType::REVERSAL->isCredit())->toBeFalse();
});

it('should return true when type is transfer', function (): void {
    expect(TransactionType::TRANSFER_OUT->isTransfer())->toBeTrue();
    expect(TransactionType::TRANSFER_IN->isTransfer())->toBeTrue();
});

it('should return false when type is not transfer', function (): void {
    expect(TransactionType::DEPOSIT->isTransfer())->toBeFalse();
    expect(TransactionType::REVERSAL->isTransfer())->toBeFalse();
});
