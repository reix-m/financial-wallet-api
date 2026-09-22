<?php

declare(strict_types=1);

namespace App\Actions\Auth\Register;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Carbon;

final class Register
{
    public function execute(RegisterInput $input): RegisterResult
    {
        $user = User::create([
            'name' => $input->name,
            'email' => $input->email,
            'password' => $input->password,
        ]);
        [$token, $expiresAt] = $this->issueToken($user);

        event(new Registered($user));

        return new RegisterResult($user, $token, 'Bearer', $expiresAt);
    }

    /**
     * @return array{0:string,1:Carbon}
     */
    private function issueToken(User $user): array
    {
        $configuredExpiration = (int) config('sanctum.expiration');
        $expiresAt = now()->addMinutes($configuredExpiration);

        $token = $user->createToken('web', $this->defaultAbilities(), $expiresAt);

        return [$token->plainTextToken, $expiresAt];
    }

    /**
     * @return list<string>
     */
    private function defaultAbilities(): array
    {
        $abilities = config('sanctum.abilities.default', []);

        return array_values(array_filter(
            array_map(static fn(mixed $ability): string => mb_trim((string) $ability), $abilities),
            static fn(string $ability): bool => '' !== $ability,
        ));
    }
}
