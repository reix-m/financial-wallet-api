<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use App\Actions\Auth\Register\RegisterInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:320'],
            'password' => ['required', 'string', Password::defaults()],
        ];
    }

    public function toInput(): RegisterInput
    {
        $this->validated();
        $data = $this->all();

        return new RegisterInput((string) $data['name'], (string) $data['email'], (string) $data['password']);
    }
}
