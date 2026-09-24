<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Wallet;

use App\Actions\Wallet\ListTransactions\ListTransactionsInput;
use Illuminate\Foundation\Http\FormRequest;

final class ListTransactionsRequest extends FormRequest
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
            'per_page' => ['nullable', 'integer', 'gt:0'],
        ];
    }

    public function toInput(int $userId): ListTransactionsInput
    {
        $data = $this->validated();
        return new ListTransactionsInput($userId, (int) $data['per_page']);
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        return array_merge($this->all(), [
            'per_page' => $this->query('per_page'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function queryParameters(): array
    {
        return [
            'per_page' => [
                'description' => 'Transactions per page',
                'example' => 15,
            ],
        ];
    }
}
