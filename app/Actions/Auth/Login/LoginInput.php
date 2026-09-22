<?php

declare(strict_types=1);

namespace App\Actions\Auth\Login;

final readonly class LoginInput
{
    public function __construct(public string $email, public string $password) {}
}
