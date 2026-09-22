<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use App\Actions\Auth\Login\LoginInput;
use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:320'],
            'password' => ['required', 'string'],
        ];
    }

    public function toInput(): LoginInput
    {
        $data = $this->validated();
        return new LoginInput((string) $data['email'], (string) $data['password']);
    }

    /**
     * @return array<string, mixed>
     */
    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'User email address',
                'example' => 'foo.bar@example.com',
            ],
            'password' => [
                'description' => 'User password',
                'example' => 'StrongP@ss123',
            ],
        ];
    }
}
