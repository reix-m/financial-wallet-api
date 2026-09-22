<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use App\Actions\Auth\VerifyEmail\VerifyEmailInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\UrlParam;

#[UrlParam('id', type: 'int', description: 'User id.', required: true, example: 1)]
#[UrlParam('hash', type: 'string', description: 'Hash of the email.', required: true, example: '2a97f4c2219118fff9108dac87093178959955a2')]
final class VerifyEmailRequest extends FormRequest
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
            'id' => ['required', 'integer', Rule::exists('users', 'id')],
            'hash' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        return array_merge($this->all(), [
            'id' => $this->route('id'),
            'hash' => $this->route('hash'),
        ]);
    }

    public function toInput(): VerifyEmailInput
    {
        $data = $this->validated();

        return new VerifyEmailInput((int) $data['id'], (string) $data['hash']);
    }
}
