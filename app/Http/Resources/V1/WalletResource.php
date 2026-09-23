<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Domain\Wallet\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class WalletResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Wallet $wallet */
        $wallet = $this->resource;

        return [
            'id' => $wallet->getId(),
            'code' => $wallet->getCode()->getValue(),
            'balance' => $wallet->getBalance()->formatted(),
            'created_at' => $wallet->getCreatedAt()->toAtomString(),
        ];
    }
}
