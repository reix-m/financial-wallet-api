<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Domain\Wallet\Transaction;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\TransactionStatus;
use App\Domain\Wallet\ValueObjects\TransactionType;
use App\Models\Transaction as TransactionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_domain_entity_instance(): void
    {
        /** @var TransactionModel $transactionModel */
        $transactionModel = new TransactionModel([
            'id' => 7,
            'wallet_id' => 10,
            'related_transaction_id' => 5,
            'type' => TransactionType::TRANSFER_OUT,
            'amount' => Money::fromCents(1000),
            'status' => TransactionStatus::COMPLETED,
            'created_at' => now(),
        ]);

        $transaction = $transactionModel->toDomainEntity();

        $this->assertEquals(7, $transaction->getId());
        $this->assertEquals(10, $transaction->getWalletId());
        $this->assertEquals(5, $transaction->getRelatedTransactionId());
        $this->assertEquals(TransactionType::TRANSFER_OUT, $transaction->getType());
        $this->assertEquals(TransactionStatus::COMPLETED, $transaction->getStatus());
        $this->assertEquals($transactionModel->amount->toCents(), $transaction->getAmount()->toCents());
        $this->assertEquals($transactionModel->created_at, $transaction->getCreatedAt());
    }

    public function test_can_create_instance_from_domain_entity(): void
    {
        $transaction = new Transaction(
            status: TransactionStatus::COMPLETED,
            type: TransactionType::TRANSFER_OUT,
            amount: Money::fromCents(1000),
            walletId: 10,
            id: 7,
            relatedTransactionId: 5,
        );

        $transactionModel = TransactionModel::fromDomainEntity($transaction);

        $this->assertEquals(7, $transactionModel->id);
        $this->assertEquals(10, $transactionModel->wallet_id);
        $this->assertEquals(5, $transactionModel->related_transaction_id);
        $this->assertEquals(TransactionType::TRANSFER_OUT, $transactionModel->type);
        $this->assertEquals(TransactionStatus::COMPLETED, $transactionModel->status);
        $this->assertEquals($transaction->getAmount()->toCents(), $transactionModel->amount->toCents());
    }

    public function test_can_create_instance_from_domain_entity_without_id(): void
    {
        $transaction = new Transaction(
            status: TransactionStatus::COMPLETED,
            type: TransactionType::DEPOSIT,
            amount: Money::fromCents(1000),
            walletId: 10,
        );

        $transactionModel = TransactionModel::fromDomainEntity($transaction);

        $this->assertNull($transactionModel->id);
        $this->assertEquals(10, $transactionModel->wallet_id);
        $this->assertEquals(TransactionType::DEPOSIT, $transactionModel->type);
    }
}
