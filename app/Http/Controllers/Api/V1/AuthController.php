<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\Register\Register;
use App\Actions\Auth\VerifyEmail\VerifyEmail;
use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Http\Requests\V1\Auth\VerifyEmailRequest;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;
use Knuckles\Scribe\Attributes\UrlParam;
use Knuckles\Scribe\Attributes\Unauthenticated;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

#[Group(name: 'Auth')]
#[Subgroup(name: 'Authentication')]
final class AuthController
{
    #[Endpoint(title: 'Register', description: 'Create a new account.')]
    #[Unauthenticated]
    #[BodyParam('name', type: 'string', required: true, example: 'Foo Bar')]
    #[BodyParam('email', type: 'string', required: true, example: 'foo.bar@example.com')]
    #[BodyParam('password', type: 'string', required: true, example: 'StrongP@ss123')]
    #[Response(
        content: [
            'user' => [
                'id' => 1,
                'name' => 'Foo Bar',
                'email' => 'foo.bar@example.com',
            ],
            'access_token' => '2|example-token',
            'token_type' => 'Bearer',
            'expiresAt' => '2026-09-22T14:32:12+00:00',
        ],
        status: SymfonyResponse::HTTP_CREATED,
        description: 'Success',
    )]
    #[Response(
        content: [
            'message' => 'The given data was invalid.',
            'errors' => ['email' => ['The email has already been taken.']],
        ],
        status: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation failed',
    )]
    public function register(RegisterRequest $request, Register $register): JsonResponse
    {
        $input = $request->toInput();
        $result = $register->execute($input);

        return response()->json([
            'user' => [
                'id' => $result->user->id,
                'name' => $result->user->name,
                'email' => $result->user->email,
            ],
            'access_token' => $result->accessToken,
            'token_type' => $result->tokenType,
            'expires_at' => $result->expiresAt->toAtomString(),
        ], SymfonyResponse::HTTP_CREATED);
    }

    #[Subgroup(name: 'Email Verification')]
    #[Endpoint(title: 'Verify Email', description: 'Verify user account.')]
    #[Unauthenticated]
    #[UrlParam('id', type: 'int', description: 'User id.', required: true, example: 1)]
    #[UrlParam('hash', type: 'string', description: 'Hash of the email.', required: true, example: '09ecbb063009fa722b45a126374dee89d060e54b')]
    #[Response(content: [], status: SymfonyResponse::HTTP_NO_CONTENT, description: 'Success')]
    #[Response(content: ['message' => 'Verification failed.'], status: SymfonyResponse::HTTP_FORBIDDEN, description: 'Invalid signed url')]
    public function verifyEmail(VerifyEmailRequest $request, VerifyEmail $verifyEmail): JsonResponse
    {
        $input = $request->toInput();
        $result = $verifyEmail->execute($input);

        if ($result) {
            return response()->json([], SymfonyResponse::HTTP_NO_CONTENT);
        }

        return response()->json(['message' => 'Verification failed.'], SymfonyResponse::HTTP_FORBIDDEN);
    }
}
