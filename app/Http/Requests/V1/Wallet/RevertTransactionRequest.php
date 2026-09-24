<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Wallet;

use App\Actions\Wallet\RevertTransaction\RevertTransactionInput;
use Illuminate\Foundation\Http\FormRequest;

final class RevertTransactionRequest extends FormRequest
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
            'transaction_id' => ['required', 'integer', 'gt:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        return array_merge($this->all(), [
            'transaction_id' => $this->route('transaction_id'),
        ]);
    }

    public function toInput(int $userId): RevertTransactionInput
    {
        $data = $this->validated();
        return new RevertTransactionInput((int) $data['transaction_id'], $userId);
    }
}
