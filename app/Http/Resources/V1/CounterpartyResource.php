<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CounterpartyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Wallet $wallet */
        $wallet = $this->resource;

        return [
            'id' => (string) $wallet->id,
            'code' => $wallet->code,
            'user_name' => $wallet->user->name ?? 'Usuário do Sistema',
        ];
    }
}
