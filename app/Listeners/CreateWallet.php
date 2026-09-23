<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\Wallet\CreateWallet\CreateWallet as AppCreateWallet;
use App\Actions\Wallet\CreateWallet\CreateWalletInput;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

final class CreateWallet
{
    public function __construct(
        private AppCreateWallet $createWallet,
    ) {}

    public function handle(Verified $event): void
    {
        /** @var User $user */
        $user = $event->user;
        $input = new CreateWalletInput(
            userId: $user->id,
        );
        $this->createWallet->execute($input);
    }
}
