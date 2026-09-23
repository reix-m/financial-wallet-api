<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Domain\Wallet\Services\RandomWalletCodeGenerator;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletCode;
use App\Domain\Wallet\Wallet;
use App\Models\Wallet as WalletModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_domain_entity_instance(): void
    {
        /** @var WalletModel $walletModel */
        $walletModel = new WalletModel([
            'user_id' => 1,
            'code' => '123456',
            'balance' => Money::fromCents(1000000),
            'created_at' => now(),
        ]);

        $wallet = $walletModel->toDomainEntity();

        $this->assertEquals(1, $wallet->getUserId());
        $this->assertInstanceOf(WalletCode::class, $wallet->getCode());
        $this->assertEquals($walletModel->balance->toCents(), $wallet->getBalance()->toCents());
        $this->assertEquals($walletModel->created_at, $wallet->getCreatedAt());
    }

    public function test_can_create_instance_from_domain_entity(): void
    {
        $codeGenerator = new RandomWalletCodeGenerator();
        $code = $codeGenerator->generate();
        $userId = 10;
        $initalBalance = Money::fromCents(1100000);
        $wallet = new Wallet($code, $userId, $initalBalance, id: 1);

        $walletModel = WalletModel::fromDomainEntity($wallet);

        $this->assertEquals(1, $walletModel->id);
        $this->assertEquals(10, $walletModel->user_id);
        $this->assertEquals($code, $walletModel->code);
        $this->assertEquals($initalBalance->toCents(), $walletModel->balance->toCents());
    }
}
