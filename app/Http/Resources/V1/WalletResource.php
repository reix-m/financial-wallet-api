<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Domain\Wallet\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

final class WalletResource extends JsonApiResource
{
    public function toId(Request $request): string
    {
        /** @var Wallet $wallet */
        $wallet = $this->resource;

        return (string) $wallet->getId();
    }

    public function toType(Request $request): string
    {
        return 'wallets';
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(Request $request): array
    {
        /** @var Wallet $wallet */
        $wallet = $this->resource;

        return [
            'code' => $wallet->getCode()->getValue(),
            'balance' => $wallet->getBalance()->formatted(),
            'created_at' => $wallet->getCreatedAt()->toAtomString(),
        ];
    }
}
