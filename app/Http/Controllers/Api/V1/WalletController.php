<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Wallet\CreateWallet\CreateWallet;
use App\Actions\Wallet\CreateWallet\CreateWalletInput;
use App\Actions\Wallet\Deposit\Deposit;
use App\Actions\Wallet\Transfer\Transfer;
use App\Http\Requests\V1\Wallet\DepositRequest;
use App\Http\Requests\V1\Wallet\TransferRequest;
use App\Http\Resources\V1\WalletResource;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

#[Group(name: 'Wallet')]
#[Authenticated]
#[Response(content: ['message' => 'Unauthorized.'], status: SymfonyResponse::HTTP_UNAUTHORIZED, description: 'Authentication failed.')]
#[Response(content: ['message' => 'Your email address is not verified.'], status: SymfonyResponse::HTTP_UNAUTHORIZED, description: 'Email is not verified.')]
final class WalletController
{
    #[Endpoint(title: 'Create wallet', description: 'Return created wallet.')]
    #[Response(
        status: SymfonyResponse::HTTP_CREATED,
        description: 'Success.',
        content: [
            'data' => [
                'type' => 'wallets',
                'id' => '1',
                'attributes' => [
                    'code' => '123456',
                    'balance' => 'R$ 0,00',
                    'created_at' => '2026-09-23T14:00:00+00:00',
                ],
            ],
        ],
    )]
    public function store(Request $request, #[CurrentUser] User $user, CreateWallet $createWallet): JsonResponse
    {
        $input = new CreateWalletInput(userId: $user->id);
        $result =  $createWallet->execute($input);

        return WalletResource::make($result)->response()->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    #[Endpoint(title: 'Deposit', description: 'Deposits an amount into wallet.')]
    #[Response(
        status: SymfonyResponse::HTTP_OK,
        description: 'Success.',
        content: [
            'data' => [
                'type' => 'wallets',
                'id' => '1',
                'attributes' => [
                    'code' => '123456',
                    'balance' => 'R$ 1,00',
                    'created_at' => '2026-09-23T14:00:00+00:00',
                ],
            ],
        ],
    )]
    public function deposit(DepositRequest $request, #[CurrentUser] User $user, Deposit $deposit): JsonResponse
    {
        $input = $request->toInput(userId: $user->id);
        $result =  $deposit->execute($input);

        return WalletResource::make($result)->response()->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    public function transfer(TransferRequest $request, #[CurrentUser] User $user, Transfer $transfer): JsonResponse
    {
        $input = $request->toInput(userId: $user->id);
        $result =  $transfer->execute($input);

        return WalletResource::make($result)->response()->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
