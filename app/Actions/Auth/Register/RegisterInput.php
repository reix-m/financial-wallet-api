<?php

declare(strict_types=1);

namespace App\Actions\Auth\Register;

final readonly class RegisterInput
{
    public function __construct(public string $name, public string $email, public string $password) {}
}
