<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Wallet;

use App\Actions\Wallet\Transfer\TransferInput;
use Illuminate\Foundation\Http\FormRequest;

final class TransferRequest extends FormRequest
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
            'target_account_code' => ['required', 'string'],
            'amount' => ['required', 'integer', 'gt:0'],
        ];
    }

    public function toInput(int $userId): TransferInput
    {
        $data = $this->validated();
        return new TransferInput($userId, (string) $data['target_account_code'], (int) $data['amount']);
    }

    /**
     * @return array<string, mixed>
     */
    public function bodyParameters(): array
    {
        return [
            'amount' => [
                'description' => 'Amount to transfer',
                'example' => 132500,
            ],
            'target_account_code' => [
                'description' => 'Target account to send transfer',
                'example' => '213431',
            ],
        ];
    }
}
