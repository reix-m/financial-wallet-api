<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Wallet\CreateWallet\CreateWallet;
use App\Actions\Wallet\CreateWallet\CreateWalletInput;
use App\Actions\Wallet\Deposit\Deposit;
use App\Actions\Wallet\Deposit\DepositInput;
use App\Actions\Wallet\RevertTransaction\RevertTransaction;
use App\Actions\Wallet\RevertTransaction\RevertTransactionInput;
use App\Actions\Wallet\Transfer\Transfer;
use App\Actions\Wallet\Transfer\TransferInput;
use App\Domain\Wallet\ValueObjects\TransactionStatus;
use App\Domain\Wallet\ValueObjects\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed two verified demo users with wallets and a small transaction history.
     *
     * Credentials: alice@example.com / bob@example.com, password "password".
     */
    public function run(): void
    {
        $alice = $this->demoUser('Alice Souza', 'alice@example.com');
        $bob = $this->demoUser('Bob Lima', 'bob@example.com');

        if (Wallet::query()->where('user_id', $alice->id)->exists()) {
            return;
        }

        app(CreateWallet::class)->execute(new CreateWalletInput($alice->id));
        $bobWallet = app(CreateWallet::class)->execute(new CreateWalletInput($bob->id));
        app(Deposit::class)->execute(new DepositInput(50000, $alice->id));
        app(Deposit::class)->execute(new DepositInput(10000, $bob->id));

        app(Transfer::class)->execute(new TransferInput(
            $alice->id,
            $bobWallet->getCode()->getValue(),
            15000,
        ));

        $bobDeposit = Transaction::query()
            ->where('wallet_id', (int) $bobWallet->getId())
            ->where('type', TransactionType::DEPOSIT)
            ->where('status', TransactionStatus::COMPLETED)
            ->first();

        if (null !== $bobDeposit) {
            app(RevertTransaction::class)->execute(new RevertTransactionInput($bobDeposit->id, $bob->id));
        }
    }

    private function demoUser(string $name, string $email): User
    {
        /** @var User $user */
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => 'password'],
        );

        if ( ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $user;
    }
}
