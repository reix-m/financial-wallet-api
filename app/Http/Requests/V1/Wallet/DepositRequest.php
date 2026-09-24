<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Wallet;

use App\Actions\Wallet\Deposit\DepositInput;
use Illuminate\Foundation\Http\FormRequest;

final class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'gt:0'],
        ];
    }

    public function toInput(int $userId): DepositInput
    {
        $data = $this->validated();
        return new DepositInput((int) $data['amount'], $userId);
    }

    /**
     * @return array<string, mixed>
     */
    public function bodyParameters(): array
    {
        return [
            'amount' => [
                'description' => 'Amount to deposit',
                'example' => 132500,
            ],
        ];
    }
}
