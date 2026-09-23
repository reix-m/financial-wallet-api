<?php

declare(strict_types=1);

namespace App\Actions\Auth\VerifyEmail;

use App\Models\User;
use Illuminate\Auth\Events\Verified;

final class VerifyEmail
{
    public function execute(VerifyEmailInput $input): bool
    {
        $user = User::query()->findOrFail($input->id);
        if ( ! hash_equals(sha1($user->getEmailForVerification()), $input->hash)) {
            return false;
        }

        if ( ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return true;
    }
}
