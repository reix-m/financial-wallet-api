<?php

declare(strict_types=1);

namespace App\Actions\Auth\VerifyEmail;

final readonly class VerifyEmailInput
{
    public function __construct(public int $id, public string $hash) {}
}
