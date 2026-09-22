<?php

declare(strict_types=1);

namespace App\Actions\Auth\Register;

use App\Models\User;
use Carbon\Carbon;

final readonly class RegisterResult
{
    public function __construct(
        public User $user,
        public string $accessToken,
        public string $tokenType,
        public Carbon $expiresAt,
    ) {}
}
