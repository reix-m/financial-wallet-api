<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\Login\Login;
use App\Actions\Auth\Register\Register;
use App\Actions\Auth\VerifyEmail\VerifyEmail;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Http\Requests\V1\Auth\VerifyEmailRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Subgroup;
use Knuckles\Scribe\Attributes\Unauthenticated;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

#[Group(name: 'Auth')]
final class AuthController
{
    #[Subgroup(name: 'Authentication')]
    #[Endpoint(title: 'Register', description: 'Create a new account.')]
    #[Unauthenticated]
    #[ResponseFromApiResource(
        name: UserResource::class,
        model: User::class,
        additional: [
            'meta' => [
                'access_token' => '2|example-token',
                'token_type' => 'Bearer',
                'expiresAt' => '2026-09-22T14:32:12+00:00',
            ]
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

        return UserResource::make($result->user)
            ->additional([
                'meta' => [
                    'access_token' => $result->accessToken,
                    'token_type' => $result->tokenType,
                    'expires_at' => $result->expiresAt->toAtomString(),
                ],
            ])
            ->response()->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    #[Subgroup(name: 'Email Verification')]
    #[Endpoint(title: 'Verify Email', description: 'Verify user account.')]
    #[Unauthenticated]
    #[Response(content: [], status: SymfonyResponse::HTTP_NO_CONTENT, description: 'Success')]
    #[Response(content: ['message' => 'Verification failed.'], status: SymfonyResponse::HTTP_FORBIDDEN, description: 'Invalid signed url')]
    public function verifyEmail(VerifyEmailRequest $request, VerifyEmail $verifyEmail): JsonResponse
    {
        $input = $request->toInput();
        $result = $verifyEmail->execute($input);

        if ($result) {
            return response()->json([], SymfonyResponse::HTTP_NO_CONTENT);
        }

        return response()->json(['message' => "Verification failed. Hash{$input->hash}."], SymfonyResponse::HTTP_FORBIDDEN);
    }

    #[Subgroup(name: 'Authentication')]
    #[Endpoint(title: 'Login', description: 'Send valid username and password and receive a token.')]
    #[Unauthenticated]
    #[ResponseFromApiResource(
        name: UserResource::class,
        model: User::class,
        additional: [
            'meta' => [
                'access_token' => '2|example-token',
                'token_type' => 'Bearer',
                'expiresAt' => '2026-09-22T14:32:12+00:00',
            ]
        ],
        status: SymfonyResponse::HTTP_OK,
        description: 'Success',
    )]
    #[Response(
        content: [
            'message' => 'Invalid credentials.',
        ],
        status: SymfonyResponse::HTTP_UNAUTHORIZED,
        description: 'Validation failed',
    )]
    public function login(LoginRequest $request, Login $login): JsonResponse
    {
        $input = $request->toInput();
        $result = $login->execute($input);

        if ( ! $result) {
            return response()->json(['message' => 'Invalid credentials.'], SymfonyResponse::HTTP_UNAUTHORIZED);
        }

        return UserResource::make($result->user)
            ->additional([
                'meta' => [
                    'access_token' => $result->accessToken,
                    'token_type' => $result->tokenType,
                    'expires_at' => $result->expiresAt->toAtomString(),
                ],
            ])
            ->response();
    }

    #[Subgroup(name: 'Authentication')]
    #[Endpoint(title: 'Me', description: 'Return authenticated user.')]
    #[Authenticated]
    #[ResponseFromApiResource(name: UserResource::class, model: User::class, status: SymfonyResponse::HTTP_OK, description: 'Authenticated user.')]
    #[Response(content: ['message' => 'Unauthorized.'], status: SymfonyResponse::HTTP_UNAUTHORIZED, description: 'Authentication failed.')]
    public function me(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        return UserResource::make($user)->response();
    }
}
