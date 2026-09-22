<?php

declare(strict_types=1);

namespace App\Actions\Auth\Login;

use App\Models\User;
use Carbon\Carbon;

final readonly class LoginResult
{
    public function __construct(
        public User $user,
        public string $accessToken,
        public string $tokenType,
        public Carbon $expiresAt,
    ) {}
}
